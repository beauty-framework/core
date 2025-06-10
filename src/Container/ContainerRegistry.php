<?php
declare(strict_types=1);

namespace Beauty\Core\Container;

use Psr\Container\ContainerInterface;

final class ContainerRegistry
{
    /**
     * @var ContainerInterface|null
     */
    private static ContainerInterface|null $container = null;

    /**
     * @param ContainerInterface $container
     * @return void
     */
    public static function set(ContainerInterface $container): void
    {
        self::$container = $container;
    }

    /**
     * @return ContainerInterface
     */
    public static function get(): ContainerInterface
    {
        if (!self::$container) {
            throw new \RuntimeException('Container not initialized');
        }

        return self::$container;
    }
}