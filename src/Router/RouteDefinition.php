<?php
declare(strict_types=1);

namespace Beauty\Core\Router;

final readonly class RouteDefinition
{
    /**
     * @param string $httpMethod
     * @param RoutePath $path
     * @param string $controllerClass
     * @param string $controllerMethod
     * @param array $middlewares
     */
    public function __construct(
        public string    $httpMethod,
        public RoutePath $path,
        public string    $controllerClass,
        public string    $controllerMethod,
        public array     $middlewares = [],
    )
    {
    }
}