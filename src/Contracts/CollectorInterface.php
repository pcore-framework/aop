<?php

declare(strict_types=1);

namespace PCore\Aop\Contracts;

/**
 * Interface CollectorInterface
 * @package PCore\Aop\Contracts
 * @github https://github.com/pcore-framework/aop
 */
interface CollectorInterface
{

    public static function collectClass(string $class, object $attribute): void;

    public static function collectMethod(string $class, string $method, object $attribute): void;

    public static function collectProperty(string $class, string $property, object $attribute): void;

    public static function collectorMethodParameter(string $class, string $method, string $parameter, object $attribute);

}