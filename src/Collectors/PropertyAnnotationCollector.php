<?php

declare(strict_types=1);

namespace PCore\Aop\Collectors;

use PCore\Aop\Contracts\PropertyAnnotation;

/**
 * Class PropertyAnnotationCollector
 * @package PCore\Aop\Collectors
 * @github https://github.com/pcore-framework/aop
 */
class PropertyAnnotationCollector extends AbstractCollector
{

    protected static array $container = [];

    /**
     * Сбор аннотаций атрибутов
     */
    public static function collectProperty(string $class, string $property, object $attribute): void
    {
        if (self::isValid($attribute)) {
            self::$container[$class][$property][] = $attribute;
        }
    }

    /**
     * Возвращает все атрибуты и аннотации класса, содержащего атрибуты
     */
    public static function getByClass(string $class): array
    {
        return self::$container[$class] ?? [];
    }

    /**
     * Возвращает аннотацию для свойства определенного класса
     *
     * @return PropertyAnnotation[]
     */
    public static function getByProperty(string $class, string $property): array
    {
        return self::$container[$class][$property] ?? [];
    }

    /**
     * Возврат собранных классов
     *
     * @return string[]
     */
    public static function getCollectedClasses(): array
    {
        return array_keys(self::$container);
    }

    /**
     * Проверяет возможности сборки
     */
    protected static function isValid(object $attribute): bool
    {
        return $attribute instanceof PropertyAnnotation;
    }

}