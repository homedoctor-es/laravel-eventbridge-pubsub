<?php

namespace HomedoctorEs\EventBridgePubSub\Values;


use Illuminate\Support\Collection;

class EventBridgeEvents
{

    protected Collection $events;

    public function __construct()
    {
        $this->events = new Collection();
    }

    public function addEvent(EventBridgeEvent $event)
    {
        $this->events->add($event);
    }

    public function events(): Collection
    {
        return $this->events;
    }

    public function toArray(): array
    {
        $events = [];

        /**
         * @var EventBridgeEvent $event
         */
        foreach ($this->events() as $event) {
            $events[] = $event->toArray();
        }

        return $events;
    }

}