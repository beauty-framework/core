<?php
declare(strict_types=1);

namespace Beauty\Core\Console\Commands\Generate;

use Beauty\Cli\Commands\Generate\AbstractGeneratorCommand;

class ListenerCommand extends AbstractGeneratorCommand
{

    /**
     * @return string
     */
    public function name(): string
    {
        return 'generate:listener';
    }

    /**
     * @return string|null
     */
    public function description(): string|null
    {
        return 'Create a new listener';
    }

    /**
     * @return string
     */
    protected function stubPath(): string
    {
        return __DIR__ . '/../../../../stubs/listener.stub';
    }

    /**
     * @return string
     */
    protected function baseNamespace(): string
    {
        return 'App\Listeners';
    }

    /**
     * @return string
     */
    protected function baseDirectory(): string
    {
        return 'app/Listeners';
    }

    /**
     * @return string
     */
    protected function suffix(): string
    {
        return 'Listener';
    }
}