<?php
declare(strict_types=1);

namespace Beauty\Core\Events;

use Psr\EventDispatcher\ListenerProviderInterface;

class ListenerProvider implements ListenerProviderInterface
{
    /** @var array<string, list<callable>> */
    protected array $listeners = [];

    public function getListenersForEvent(object $event): iterable
    {
        $eventType = $event::class;

        foreach ($this->listeners[$eventType] ?? [] as $listener) {
            yield $listener;
        }
    }

    public function addListener(string $event, callable $listener): void
    {
        $this->listeners[$event][] = $listener;
    }
}