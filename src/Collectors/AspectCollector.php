<?php

declare(strict_types=1);

namespace PCore\Aop\Collectors;

use PCore\Aop\Annotations\AspectConfig;
use PCore\Aop\Contracts\AspectInterface;
use PCore\Aop\Scanner;
use PCore\Di\Reflection;
use ReflectionException;

/**
 * Class AspectCollector
 * @package PCore\Aop\Collectors
 * @github https://github.com/pcore-framework/aop
 */
class AspectCollector extends AbstractCollector
{

    protected static array $container = [];

    /**
     * Раздел метода сбора
     * @param string $class
     * @param string $method
     * @param object $attribute
     */
    public static function collectMethod(string $class, string $method, object $attribute): void
    {
        if ($attribute instanceof AspectInterface) {
            self::$container[$class][$method][] = $attribute;
        }
    }

    /**
     * Возвращает аспект метода класса
     * @param string $class
     * @param string $method
     * @return array
     */
    public static function getMethodAspects(string $class, string $method): array
    {
        return self::$container[$class][$method] ?? [];
    }

    /**
     * Возвращает собранный класс
     *
     * @return string[]
     */
    public static function getCollectedClasses(): array
    {
        return array_keys(self::$container);
    }

    /**
     * @throws ReflectionException
     */
    public static function collectClass(string $class, object $attribute): void
    {
        if ($attribute instanceof AspectInterface) {
            foreach (Reflection::class($class)->getMethods() as $reflectionMethod) {
                if (!$reflectionMethod->isConstructor()) {
                    self::$container[$class][$reflectionMethod->getName()][] = $attribute;
                }
            }
        } else if ($attribute instanceof AspectConfig) {
            $reflectionClass = Reflection::class($attribute->class);
            $annotation = new $class(...$attribute->params);
            $methods = $attribute->methods;
            if ($methods === '*') {
                foreach ($reflectionClass->getMethods() as $reflectionMethod) {
                    if (!$reflectionMethod->isConstructor()) {
                        self::$container[$attribute->class][$reflectionMethod->getName()][] = $annotation;
                    }
                }
            } else {
                foreach ((array)$methods as $method) {
                    self::$container[$attribute->class][$method][] = $annotation;
                }
            }
            Scanner::addClass($attribute->class, $reflectionClass->getFileName());
        }
    }

}