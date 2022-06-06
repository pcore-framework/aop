<?php

declare(strict_types=1);

namespace PCore\Aop;

use ArrayObject;
use Closure;

/**
 * Class JoinPoint
 * @package PCore\Aop
 * @github https://github.com/pcore-framework/aop
 */
class JoinPoint
{

    /**
     * @param object $object вырезать в текущем экземпляре класса
     * @param string $method
     * @param ArrayObject $parameters список параметров, переданных текущим методом
     * @param Closure $callback
     */
    public function __construct(
        public object $object,
        public string $method,
        public ArrayObject $parameters,
        protected Closure $callback
    )
    {
    }

    /**
     * Выполнить прокси-метод
     */
    public function process(): mixed
    {
        return call_user_func_array($this->callback, $this->parameters->getArrayCopy());
    }

}