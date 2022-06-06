<?php

namespace PCore\Aop;

use PCore\Utils\Traits\AutoFillProperties;

/**
 * Class ScannerConfig
 * @package PCore\Aop
 * @github https://github.com/pcore-framework/aop
 */
class ScannerConfig
{

    use AutoFillProperties;

    protected bool $cache = false;
    protected array $paths = [];
    protected array $collectors = [];
    protected string $runtimeDir = '';

    public function __construct(array $options)
    {
        $this->fillProperties($options);
    }

    public function isCache(): bool
    {
        return $this->cache;
    }

    public function getPaths(): array
    {
        return $this->paths;
    }

    public function getCollectors(): array
    {
        return $this->collectors;
    }

    public function getRuntimeDir(): string
    {
        return rtrim($this->runtimeDir, '/\\');
    }

}