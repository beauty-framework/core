<?php
declare(strict_types=1);

use Beauty\Core\Container\ContainerRegistry;

if (!function_exists('container')) {

    /**
     * @return \Psr\Container\ContainerInterface
     */
    function container(): \Psr\Container\ContainerInterface
    {
        return ContainerRegistry::get();
    }
}