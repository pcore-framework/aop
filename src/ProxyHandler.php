<?php

declare(strict_types=1);

namespace PCore\Aop;

use ArrayObject;
use Closure;
use PCore\Aop\Collectors\{AspectCollector, AspectInterface};
use PCore\Di\Reflection;
use ReflectionException;

/**
 * Trait ProxyHandler
 * @package PCore\Aop
 * @github https://github.com/pcore-framework/aop
 */
trait ProxyHandler
{

    protected static array $__aspectCache = [];

    /**
     * @throws ReflectionException
     */
    protected function __callViaProxy(string $method, Closure $callback, array $parameters): mixed
    {
        if (!isset(static::$__aspectCache[$method])) {
            static::$__aspectCache[$method] = array_reverse([
                ...AspectCollector::getClassAspects(__CLASS__),
                ...AspectCollector::getMethodAspects(__CLASS__, $method)
            ]);
        }
        /** @var AspectInterface $aspect */
        $pipeline = array_reduce(
            self::$__aspectCache[$method],
            fn($stack, $aspect) => fn(JoinPoint $joinPoint) => $aspect->process($joinPoint, $stack),
            fn(JoinPoint $joinPoint) => $joinPoint->process()
        );
        return $pipeline(
            new JoinPoint($this, $method, new ArrayObject(
                array_combine(Reflection::methodParameterNames(__CLASS__, $method), $parameters)
            ), $callback)
        );
    }

}