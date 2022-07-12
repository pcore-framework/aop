<?php

declare(strict_types=1);

namespace PCore\Aop\Annotations;

use Attribute;

/**
 * Class AspectConfig
 * @package PCore\Aop\Annotations
 * @github https://github.com/pcore-framework/aop
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class AspectConfig
{

    /**
     * @param string $class
     * @param string $method
     * @param array $params
     */
    public function __construct(
        public string $class,
        public string $method = '*',
        public array $params = []
    )
    {
    }

}