<?php

declare(strict_types=1);

namespace PCore\Aop\Contracts;

use Closure;
use PCore\Aop\JoinPoint;

/**
 * Interface AspectInterface
 * @package PCore\Aop\Contracts
 * @github https://github.com/pcore-framework/aop
 */
interface AspectInterface
{

    public function process(JoinPoint $joinPoint, Closure $next): mixed;

}