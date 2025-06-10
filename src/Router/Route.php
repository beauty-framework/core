<?php
declare(strict_types=1);

namespace Beauty\Core\Router;

use Beauty\Http\Enums\HttpMethodsEnum;

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class Route
{
    /**
     * @param HttpMethodsEnum $method
     * @param string $path
     */
    public function __construct(
        public HttpMethodsEnum $method,
        public string $path,
    )
    {
    }
}