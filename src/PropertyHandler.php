<?php

declare(strict_types=1);

namespace PCore\Aop;

use PCore\Aop\Collectors\PropertyAnnotationCollector;

/**
 * Trait PropertyHandler
 * @package PCore\Aop
 * @github https://github.com/pcore-framework/aop
 */
trait PropertyHandler
{

    protected bool $__propertyHandled = false;

    protected function __handleProperties(): void
    {
        if (!$this->__propertyHandled) {
            foreach (PropertyAnnotationCollector::getByClass(self::class) as $property => $attributes) {
                foreach ($attributes as $attribute) {
                    $attribute->handle($this, $property);
                }
            }
            $this->__propertyHandled = true;
        }
    }

}