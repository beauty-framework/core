<?php
declare(strict_types=1);

namespace Beauty\Core\Console\Commands\Generate;

use Beauty\Cli\Commands\Generate\AbstractGeneratorCommand;

class EventCommand extends AbstractGeneratorCommand
{

    /**
     * @return string
     */
    public function name(): string
    {
        return 'generate:event';
    }

    /**
     * @return string|null
     */
    public function description(): string|null
    {
        return 'Create a new event';
    }

    /**
     * @return string
     */
    protected function stubPath(): string
    {
        return __DIR__ . '/../../../../stubs/event.stub';
    }

    /**
     * @return string
     */
    protected function baseNamespace(): string
    {
        return 'App\Events';
    }

    /**
     * @return string
     */
    protected function baseDirectory(): string
    {
        return 'app/Events';
    }

    /**
     * @return string
     */
    protected function suffix(): string
    {
        return 'Event';
    }
}