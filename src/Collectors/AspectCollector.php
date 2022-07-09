<?php

declare(strict_types=1);

namespace PCore\Aop\Collectors;

use PCore\Aop\Contracts\AspectInterface;

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
        if (self::isValid($attribute)) {
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
     * @return AspectInterface[]
     */
    public static function getCollectedClasses(): array
    {
        return array_keys(self::$container);
    }

    /**
     * Проверяет возможности сборки
     * @param object $attribute
     * @return bool
     */
    public static function isValid(object $attribute): bool
    {
        return $attribute instanceof AspectInterface;
    }

}