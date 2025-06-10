<?php
declare(strict_types=1);

namespace Beauty\Core\Container;

use Psr\Container\ContainerInterface;

interface ContainerAwareInterface
{
    /**
     * @param ContainerInterface $container
     * @return void
     */
    public function setContainer(ContainerInterface $container): void;
}
