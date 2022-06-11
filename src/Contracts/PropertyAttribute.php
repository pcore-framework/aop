<?php

declare(strict_types=1);

namespace PCore\Aop\Contracts;

/**
 * Interface PropertyAttribute
 * @package PCore\Aop\Contracts
 * @github https://github.com/pcore-framework/aop
 */
interface PropertyAttribute
{

    public function handle(object $object, string $property): void;

}