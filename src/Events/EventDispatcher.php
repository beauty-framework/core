<?php
declare(strict_types=1);

namespace Beauty\Core\Events;

use Psr\EventDispatcher\EventDispatcherInterface;

class EventDispatcher implements EventDispatcherInterface
{
    /**
     * @param ListenerProvider $provider
     */
    public function __construct(
        protected ListenerProvider $provider,
    )
    {
    }

    /**
     * @param object $event
     * @return object
     */
    public function dispatch(object $event): object
    {
        foreach($this->provider->getListenersForEvent($event) as $listener) {
            $listener($event);
        }

        return $event;
    }
}