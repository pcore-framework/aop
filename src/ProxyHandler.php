<?php

declare(strict_types=1);

namespace PCore\Aop;

use ArrayObject;
use Closure;
use PCore\Aop\Collectors\AspectCollector;
use PCore\Aop\Contracts\AspectInterface;
use PCore\Di\Reflection;
use ReflectionException;

/**
 * Trait ProxyHandler
 * @package PCore\Aop
 * @github https://github.com/pcore-framework/aop
 */
trait ProxyHandler
{

    /**
     * @param string $method
     * @param Closure $callback
     * @param array $parameters
     * @return mixed
     * @throws ReflectionException
     */
    protected static function __callViaProxy(string $method, Closure $callback, array $parameters): mixed
    {
        $class = static::class;
        /** @var AspectInterface $aspect */
        $pipeline = array_reduce(
            array_reverse(AspectCollector::getMethodAspects($class, $method)),
            fn($stack, $aspect) => fn(JoinPoint $joinPoint) => $aspect->process($joinPoint, $stack),
            fn(JoinPoint $joinPoint) => $joinPoint->process()
        );
        $funcArgs = new ArrayObject();
        $methodParameters = Reflection::methodParameterNames($class, $method);
        foreach ($parameters as $key => $parameter) {
            $funcArgs->offsetSet($methodParameters[$key], $parameter);
        }
        return $pipeline(new JoinPoint($class, $method, $funcArgs, $callback));
    }

}