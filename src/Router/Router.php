<?php
declare(strict_types=1);

namespace Beauty\Core\Router;

use Beauty\Core\Files\PathNameTrait;
use Beauty\Core\Router\Exceptions\NotFoundException;
use Beauty\Http\Middleware\Middleware;
use Beauty\Http\Request\AbstractValidatedRequest;
use Beauty\Http\Response\Contracts\ResponsibleInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionNamedType;
use RuntimeException;
use Symfony\Component\Finder\Finder;

class Router
{
    use PathNameTrait;

    /**
     * @var array
     */
    protected array $routes = [];

    /**
     * @param array $controllerClasses
     * @param array $controllerPatterns
     * @param ContainerInterface|null $container
     * @throws ReflectionException
     */
    public function __construct(
        array                             $controllerClasses = [],
        array                             $controllerPatterns = [],
        protected ContainerInterface|null $container = null,
    )
    {
        foreach ($controllerPatterns as $pattern) {
            $this->registerFromGlob($pattern);
        }

        foreach ($controllerClasses as $controllerClass) {
            $this->registerRoutesFromController($controllerClass);
        }
    }

    /**
     * @param ServerRequestInterface $request
     * @return object
     * @throws NotFoundException
     */
    public function match(ServerRequestInterface $request): object
    {
        $method = $request->getMethod();
        $uri = $request->getUri()->getPath();

        /** @var RouteDefinition $route */
        foreach ($this->routes as $route) {
            if ($route->httpMethod !== $method) {
                continue;
            }

            $params = $route->path->match($uri);

            if ($params !== null) {
                return (object)[
                    'handler' => fn(ServerRequestInterface $req) => $this->invokeHandler(
                        $route->controllerClass,
                        $route->controllerMethod,
                        $req,
                        $params
                    ),
                    'middlewares' => $route->middlewares,
                ];
            }
        }

        throw new NotFoundException("Route [$method $uri] not found");
    }

    /**
     * @param string $pattern
     * @return void
     * @throws ReflectionException
     */
    protected function registerFromGlob(string $pattern): void
    {
        $dir = $this->extractDir($pattern);
        $mask = $this->extractMask($pattern);

        $finder = new Finder();
        $finder->files()->in($dir)->name($mask)->depth('>= 0');

        foreach ($finder as $file) {
            $path = $file->getRealPath();
            $className = $this->resolveClassFromFile($path);

            if (class_exists($className)) {
                $this->registerRoutesFromController($className);
            }
        }
    }

    /**
     * @param string $file
     * @return string
     */
    protected function resolveClassFromFile(string $file): string
    {
        $contents = file_get_contents($file);

        // Match namespace
        if (!preg_match('/namespace\s+(.+?);/', $contents, $nsMatch)) {
            throw new RuntimeException("Cannot determine namespace in file: $file");
        }

        // Match class
        if (!preg_match('/class\s+([a-zA-Z0-9_]+)/', $contents, $classMatch)) {
            throw new RuntimeException("Cannot determine class name in file: $file");
        }

        return $nsMatch[1] . '\\' . $classMatch[1];
    }

    /**
     * @param string $className
     * @return void
     * @throws ReflectionException
     */
    protected function registerRoutesFromController(string $className): void
    {
        $refClass = new ReflectionClass($className);

        $classMiddlewareAttributes = $refClass->getAttributes(Middleware::class);
        $classMiddlewares = $this->extractMiddlewares($classMiddlewareAttributes);

        foreach ($refClass->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            foreach ($method->getAttributes(Route::class) as $attribute) {
                /** @var Route $route */
                $route = $attribute->newInstance();

                $methodMiddlewareAttributes = $method->getAttributes(Middleware::class);
                $methodMiddlewares = $this->extractMiddlewares($methodMiddlewareAttributes);

                $middlewares = array_merge($classMiddlewares, $methodMiddlewares);

                $this->routes[] = new RouteDefinition(
                    httpMethod: $route->method->value,
                    path: new RoutePath($route->path),
                    controllerClass: $className,
                    controllerMethod: $method->getName(),
                    middlewares: $middlewares,
                );
            }
        }
    }

    /**
     * @param string $className
     * @param string $methodName
     * @param ServerRequestInterface $request
     * @param array $routeParams
     * @return ResponseInterface|ResponsibleInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    protected function invokeHandler(
        string                 $className,
        string                 $methodName,
        ServerRequestInterface $request,
        array                  $routeParams
    ): ResponseInterface|ResponsibleInterface
    {
        $controller = $this->container->get($className);
        $refMethod = new ReflectionMethod($controller, $methodName);

        $args = [];

        foreach ($refMethod->getParameters() as $param) {
            $paramName = $param->getName();
            $paramType = $param->getType();

            if ($paramType instanceof ReflectionNamedType) {
                $typeName = $paramType->getName();

                if (is_subclass_of($typeName, AbstractValidatedRequest::class)) {
                    $args[] = (new $typeName($request))
                        ->withBaseAttributes($request);

                    continue;
                }

                if ($typeName === ServerRequestInterface::class || is_a($typeName, ServerRequestInterface::class, true)) {
                    $args[] = $request;
                    continue;
                }

                if (array_key_exists($paramName, $routeParams)) {
                    $value = $routeParams[$paramName];

                    if ($value !== null && $paramType !== null) {
                        settype($value, $typeName);
                    }

                    $args[] = $value;
                    continue;
                }

                if ($param->isDefaultValueAvailable()) {
                    $args[] = $param->getDefaultValue();
                    continue;
                }

                if ($this->container->has($typeName)) {
                    $args[] = $this->container->get($typeName);
                    continue;
                }

                throw new RuntimeException("Cannot resolve parameter \${$paramName} of type {$typeName}");
            }

            $args[] = $routeParams[$paramName] ?? null;
        }

        return $refMethod->invokeArgs($controller, $args);
    }

    /**
     * @param array $attributes
     * @return array
     */
    protected function extractMiddlewares(array $attributes): array
    {
        $result = [];

        foreach ($attributes as $attr) {
            /** @var Middleware $instance */
            $instance = $attr->newInstance();

            if (is_array($instance->middlewares)) {
                $result = array_merge($result, $instance->middlewares);
            } else {
                $result[] = $instance->middlewares;
            }
        }

        return $result;
    }
}