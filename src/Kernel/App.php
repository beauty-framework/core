<?php
declare(strict_types=1);

namespace Beauty\Core\Kernel;

use Beauty\Core\Router\Router;
use Beauty\Http\Response\Normalizers\ResponseFactory;
use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class App
{
    /**
     * @var Router
     */
    protected Router $router;

    /**
     * @var ContainerInterface
     */
    protected ContainerInterface $container;

    /**
     * @var array
     */
    protected array $middlewares;

    /**
     * @var ResponseFactory
     */
    protected ResponseFactory $responseFactory;

    /**
     * @param ContainerInterface|null $container
     * @throws DependencyException
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function __construct(ContainerInterface|null $container = null)
    {
        $this->container = $container ?? new Container();
        $this->responseFactory = $this->container->get(ResponseFactory::class);
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws \Beauty\Core\Router\Exceptions\NotFoundException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $route = $this->router->match($request);

        $allMiddlewares = array_merge($this->middlewares, $route->middlewares);

        $handler = new class($route->handler, $this->responseFactory) implements RequestHandlerInterface {
            public function __construct(
                private $handler,
                private ResponseFactory $responseFactory,
            )
            {
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $result = call_user_func($this->handler, $request);
                return $this->responseFactory->make($result);
            }
        };

        foreach (array_reverse($allMiddlewares) as $middlewareClass) {
            $middleware = $this->container->get($middlewareClass);
            $handler = new class($middleware, $handler) implements RequestHandlerInterface {
                public function __construct(
                    private MiddlewareInterface     $middleware,
                    private RequestHandlerInterface $next,
                )
                {
                }

                public function handle(ServerRequestInterface $request): ResponseInterface
                {
                    return $this->middleware->process($request, $this->next);
                }
            };
        }

        return $handler->handle($request);
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * @param array $routerConfig
     * @return App
     * @throws \ReflectionException
     */
    public function withRouterConfig(array $routerConfig = []): App
    {
        $this->router = new Router(controllerPatterns: $routerConfig['controllers'] ?? [], container: $this->container);

        return $this;
    }

    /**
     * @param array $middlewares
     * @return $this
     */
    public function withMiddlewares(array $middlewares = []): App
    {
        $this->middlewares = $middlewares['global'] ?? [];

        return $this;
    }
}