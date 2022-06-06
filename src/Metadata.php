<?php

declare(strict_types=1);

namespace PCore\Aop;

use Composer\Autoload\ClassLoader;

class Metadata
{

    /**
     * @param string $className имя класса
     * @param bool $hasConstructor есть ли конструктор
     */
    public function __construct(
        public ClassLoader $loader,
        public string $className,
        public bool $hasConstructor = false
    )
    {
    }

}