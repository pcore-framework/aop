<?php

declare(strict_types=1);

namespace PCore\Aop;

use Attribute;
use PCore\Aop\Collectors\{AspectCollector, PropertyAnnotationCollector};
use PCore\Aop\Exceptions\ProcessException;
use PCore\Di\Reflection;
use PCore\Utils\Composer;
use PCore\Utils\Filesystem;
use PhpParser\NodeTraverser;
use PhpParser\PrettyPrinter\Standard;
use ReflectionException;
use Symfony\Component\Finder\Finder;
use Throwable;

/**
 * Class Scanner
 * @package PCore\Aop
 * @github https://github.com/pcore-framework/aop
 */
final class Scanner
{

    private static AstManager $astManager;
    private static string $runtimeDir;
    private static string $proxyMap;
    private static array $classMap = [];
    private static array $collectors = [AspectCollector::class, PropertyAnnotationCollector::class];
    private static bool $initialized = false;

    /**
     * @param ScannerConfig $config
     * @throws ReflectionException
     */
    public static function init(ScannerConfig $config): void
    {
        if (!self::$initialized) {
            self::$runtimeDir = $config->getRuntimeDir() . '/aop/';
            Filesystem::isDirectory(self::$runtimeDir) || Filesystem::makeDirectory(self::$runtimeDir, 0755, true);
            self::$astManager = new AstManager();
            self::$classMap = self::scanDir($config->getPaths());
            self::$proxyMap = $proxyMap = self::$runtimeDir . 'proxy.php';
            if (!$config->isCache() || !Filesystem::exists($proxyMap)) {
                Filesystem::exists($proxyMap) && Filesystem::delete($proxyMap);
                if (($pid = pcntl_fork()) == -1) {
                    throw new ProcessException('Сбой разветвления процесса.');
                }
                pcntl_wait($pid);
            }
            Composer::getClassLoader()->addClassMap(self::getProxyMap(self::$collectors));
            self::collect([...self::$collectors, ...$config->getCollectors()]);
            self::$initialized = true;
        }
    }

    public static function scanDir(array $dirs): array
    {
        $files = (new Finder())->in($dirs)->name('*.php')->files();
        $classes = [];
        foreach ($files as $file) {
            $realPath = $file->getRealPath();
            foreach (self::$astManager->getClassesByRealPath($realPath) as $class) {
                $classes[$class] = $realPath;
            }
        }
        return $classes;
    }

    public static function scanConfig(string $installedJsonDir): array
    {
        $installed = json_decode(file_get_contents($installedJsonDir), true);
        $installed = $installed['packages'] ?? $installed;
        $config = [];
        foreach ($installed as $package) {
            if (isset($package['extra']['pcore']['config'])) {
                $configProvider = $package['extra']['pcore']['config'];
                $configProvider = new $configProvider();
                if (method_exists($configProvider, '__invoke')) {
                    if (is_array($configItem = $configProvider())) {
                        $config = array_merge_recursive($config, $configItem);
                    }
                }
            }
        }
        return $config;
    }

    /**
     * @param string $class
     * @param string $path
     */
    public static function addClass(string $class, string $path)
    {
        self::$classMap[$class] = $path;
    }

    /**
     * @param array $collectors
     * @return array
     * @throws ReflectionException
     */
    private static function getProxyMap(array $collectors): array
    {
        if (!Filesystem::exists(self::$proxyMap)) {
            $proxyDir = self::$runtimeDir . 'proxy/';
            Filesystem::makeDirectory($proxyDir, 0755, true, true);
            Filesystem::cleanDirectory($proxyDir);
            self::collect($collectors);
            $collectedClasses = array_unique(array_merge(AspectCollector::getCollectedClasses(), PropertyAnnotationCollector::getCollectedClasses()));
            $scanMap = [];
            foreach ($collectedClasses as $class) {
                $proxyPath = $proxyDir . str_replace('\\', '_', $class) . '_Proxy.php';
                Filesystem::put($proxyPath, self::generateProxyClass($class, self::$classMap[$class]));
                $scanMap[$class] = $proxyPath;
            }
            Filesystem::put(self::$proxyMap, sprintf("<?php \nreturn %s;", var_export($scanMap, true)));
            exit;
        }
        return include self::$proxyMap;
    }

    private static function generateProxyClass(string $class, string $path): string
    {
        $ast = self::$astManager->getNodes($path);
        $traverser = new NodeTraverser();
        $metadata = new Metadata($class);
        $traverser->addVisitor(new PropertyHandlerVisitor($metadata));
        $traverser->addVisitor(new ProxyHandlerVisitor($metadata));
        $modifiedStmts = $traverser->traverse($ast);
        $prettyPrinter = new Standard();
        return $prettyPrinter->prettyPrintFile($modifiedStmts);
    }

    /**
     * @param array $collectors
     * @throws ReflectionException
     */
    private static function collect(array $collectors): void
    {
        foreach (self::$classMap as $class => $path) {
            $reflectionClass = Reflection::class($class);
            foreach ($reflectionClass->getAttributes() as $attribute) {
                $attributeInstance = $attribute->newInstance();
                if ($attributeInstance instanceof Attribute) {
                    continue;
                }
                try {
                    $attributeInstance = $attribute->newInstance();
                    foreach ($collectors as $collector) {
                        $collector::collectClass($class, $attributeInstance);
                    }
                } catch (Throwable $throwable) {
                    echo '[NOTICE] ' . $class . ': ' . $throwable->getMessage() . PHP_EOL;
                }
            }
            foreach ($reflectionClass->getProperties() as $reflectionProperty) {
                foreach ($reflectionProperty->getAttributes() as $attribute) {
                    try {
                        foreach ($collectors as $collector) {
                            $collector::collectProperty($class, $reflectionProperty->getName(), $attribute->newInstance());
                        }
                    } catch (Throwable $throwable) {
                        echo '[NOTICE] ' . $class . ': ' . $throwable->getMessage() . PHP_EOL;
                    }
                }
            }
            foreach ($reflectionClass->getMethods() as $reflectionMethod) {
                $method = $reflectionMethod->getName();
                foreach ($reflectionMethod->getAttributes() as $attribute) {
                    try {
                        foreach ($collectors as $collector) {
                            $collector::collectMethod($class, $method, $attribute->newInstance());
                        }
                    } catch (Throwable $throwable) {
                        echo '[NOTICE] ' . $class . ': ' . $throwable->getMessage() . PHP_EOL;
                    }
                }
                foreach ($reflectionMethod->getParameters() as $reflectionParameter) {
                    try {
                        foreach ($reflectionParameter->getAttributes() as $attribute) {
                            foreach ($collectors as $collector) {
                                $collector::collectorMethodParameter($class, $method, $reflectionParameter->getName(), $attribute->newInstance());
                            }
                        }
                    } catch (Throwable $throwable) {
                        echo '[NOTICE] ' . $class . ': ' . $throwable->getMessage() . PHP_EOL;
                    }
                }
            }
        }
    }
}