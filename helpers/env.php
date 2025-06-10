<?php
declare(strict_types=1);

use Beauty\Core\Config\ConfigRepository;

if (!function_exists('env')) {
    /**
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    function env(string $key, mixed $default = null): mixed
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? null;

        if ($value === null) {
            return $default;
        }

        return match (strtolower($value)) {
            'true' => true,
            'false' => false,
            'null' => null,
            default => $value,
        };
    }
}

if (!function_exists('config')) {
    /**
     * @param string|null $key
     * @param mixed|null $default
     * @return mixed
     */
    function config(string|null $key = null, mixed $default = null): mixed
    {
        $repository = \Beauty\Core\Config\ConfigRegistry::get();

        if ($repository === null) {
            throw new RuntimeException('Config repository not initialized');
        }

        if ($key === null) {
            return $repository->all();
        }

        return $repository->get($key, $default);
    }

    /**
     * @param ConfigRepository $repo
     * @return void
     */
    function set_config_repository(ConfigRepository $repo): void
    {
        \Beauty\Core\Config\ConfigRegistry::set($repo);
    }
}