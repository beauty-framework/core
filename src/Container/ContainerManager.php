<?php
declare(strict_types=1);

namespace Beauty\Core\Container;

use DI\Container;
use DI\ContainerBuilder;
use RoadRunner\Lock\Lock;
use Spiral\Goridge\RPC\RPC;
use Spiral\RoadRunner\Environment;
use function DI\autowire;
use function DI\create;
use function DI\factory;
use function DI\value;

class ContainerManager
{
    /**
     * @var array
     */
    private array $definitions = [];

    /**
     * @var array
     */
    private array $afterBoot = [];

    /**
     * @var Container|null
     */
    private Container|null $container = null;

    /**
     * @var string|null
     */
    private static string|null $cachePath = null;

    /**
     * @param string $path
     * @return void
     */
    public static function enableCache(string $path): void
    {
        self::$cachePath = $path;
    }

    /**
     * @param array $configurators
     * @return self
     * @throws \Exception
     */
    public static function bootFrom(array $configurators): self
    {
        $manager = new self();

        foreach ($configurators as $class) {
            if (!method_exists($class, 'configure')) {
                throw new \RuntimeException("Missing static method configure() in $class");
            }

            $class::configure($manager);
        }

        $manager->setDefaultContainers($manager);

        $manager->boot();

        return $manager;
    }

    /**
     * @param string $abstract
     * @param string|object $concrete
     * @return void
     */
    public function bind(string $abstract, string|object $concrete): void
    {
        if (is_string($concrete) && class_exists($concrete)) {
            $this->definitions[$abstract] = autowire($concrete);
        } else {
            $this->definitions[$abstract] = $concrete;
        }
    }

    /**
     * @param string $abstract
     * @param callable|string $resolver
     * @return void
     */
    public function singleton(string $abstract, callable|string $resolver): void
    {
        $this->definitions[$abstract] = is_callable($resolver)
            ? factory($resolver)
            : create($resolver);
    }

    /**
     * @param string $abstract
     * @param callable $resolver
     * @return void
     */
    public function factory(string $abstract, callable $resolver): void
    {
        $this->definitions[$abstract] = factory($resolver);
    }

    /**
     * @param string $abstract
     * @return void
     */
    public function autowire(string $abstract): void
    {
        $this->definitions[$abstract] = autowire($abstract);
    }

    public function instance(string $abstract, object $instance): void
    {
        $this->definitions[$abstract] = value($instance);
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function boot(): void
    {
        $builder = new ContainerBuilder();

        if (self::$cachePath !== null) {
            $builder->enableCompilation(self::$cachePath);
        }

        $builder->addDefinitions($this->definitions);
        $this->container = $builder->build();

        foreach ($this->afterBoot as $cb) {
            $cb($this);
        }
    }

    /**
     * @param callable $callback
     * @return void
     */
    public function afterBoot(callable $callback): void
    {
        $this->afterBoot[] = $callback;
    }

    /**
     * @param string $id
     * @return mixed
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function get(string $id): mixed
    {
        if (!$this->container) {
            throw new \RuntimeException("Container not booted yet");
        }

        return $this->container->get($id);
    }

    /**
     * @return Container|null
     */
    public function getContainer(): ?Container
    {
        return $this->container;
    }

    /**
     * @param ContainerManager $manager
     * @return void
     */
    protected function setDefaultContainers(self $manager): void
    {
        // $manager->bind(Lock::class, new Lock(RPC::create(Environment::fromGlobals()->getRPCAddress())));
    }

}