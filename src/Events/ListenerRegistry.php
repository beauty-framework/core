<?php
declare(strict_types=1);

namespace Beauty\Core\Events;

use Beauty\Core\Container\ContainerAwareInterface;
use Psr\Container\ContainerInterface;

class ListenerRegistry implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private ContainerInterface $container;

    /**
     * @param ListenerProvider $provider
     */
    public function __construct(
        public ListenerProvider $provider,
    )
    {
    }

    /**
     * @param class-string $eventClass
     * @param class-string|callable $listener
     */
    public function register(string $eventClass, callable|string $listener): void
    {
        if (is_string($listener)) {
            $this->provider->addListener($eventClass, function (object $event) use ($listener) {
                $resolved = $this->container->get($listener);
                return $resolved->handle($event);
            });
        } else {
            $this->provider->addListener($eventClass, $listener);
        }
    }

    /**
     * @param ContainerInterface $container
     * @return void
     */
    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }
}