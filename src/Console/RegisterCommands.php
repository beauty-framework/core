<?php
declare(strict_types=1);

namespace Beauty\Core\Console;

use Beauty\Cli\Console\Contracts\CommandsRegistryInterface;
use Beauty\Core\Console\Commands\Generate\EventCommand;
use Beauty\Core\Console\Commands\Generate\ListenerCommand;

class RegisterCommands implements CommandsRegistryInterface
{

    /**
     * @return string[]
     */
    public static function commands(): array
    {
        return [
            EventCommand::class,
            ListenerCommand::class,
        ];
    }
}