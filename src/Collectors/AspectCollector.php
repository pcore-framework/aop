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

    public static function collectMethod(string $class, string $method, object $attribute): void
    {
        if (self::isValid($attribute)) {
            self::$container['method'][$class][$method][] = $attribute;
        }
    }

    public static function collectClass(string $class, object $attribute): void
    {
        if (self::isValid($attribute)) {
            self::$container['class'][$class][] = $attribute;
        }
    }

    /**
     * Возвращает фасет метода класса
     */
    public static function getMethodAspects(string $class, string $method): array
    {
        return self::$container['method'][$class][$method] ?? [];
    }

    /**
     * Возвращает раздел определенного класса
     *
     * @return AspectInterface[]
     */
    public static function getClassAspects(string $class): array
    {
        return self::$container['class'][$class] ?? [];
    }

    /**
     * Возвращает собранные классы
     *
     * @return AspectInterface[]
     */
    public static function getCollectedClasses(): array
    {
        return array_unique([...array_keys(self::$container['class'] ?? []), ...array_keys(self::$container['method'] ?? [])]);
    }

    /**
     * Проверяет возможности сборки
     */
    public static function isValid(object $attribute): bool
    {
        return $attribute instanceof AspectInterface;
    }

}