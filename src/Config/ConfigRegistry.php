<?php
declare(strict_types=1);

namespace Beauty\Core\Config;

final class ConfigRegistry
{
    /**
     * @var ConfigRepository|null
     */
    private static ConfigRepository|null $instance = null;

    /**
     * @param ConfigRepository $repo
     * @return void
     */
    public static function set(ConfigRepository $repo): void
    {
        self::$instance = $repo;
    }

    /**
     * @return ConfigRepository
     */
    public static function get(): ConfigRepository
    {
        if (self::$instance === null) {
            throw new \RuntimeException('Config repository not initialized');
        }

        return self::$instance;
    }
}