<?php

declare(strict_types=1);

namespace PCore\Aop\Contracts;

/**
 * Interface PropertyAnnotation
 * @package PCore\Aop\Contracts
 * @github https://github.com/pcore-framework/aop
 */
interface PropertyAnnotation
{

    public function handle(object $object, string $property): void;

}