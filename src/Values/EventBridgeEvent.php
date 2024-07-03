<?php

namespace HomedoctorEs\EventBridgePubSub\Values;

class EventBridgeEvent
{

    protected string $eventBusName;
    protected Message $message;

    public function __construct(Message $message, string $eventBusName)
    {
        $this->message = $message;
        $this->eventBusName = $eventBusName;
    }

    public function eventBusName(): string
    {
        return $this->eventBusName;
    }

    public function message(): Message
    {
        return $this->message;
    }

    public function toArray()
    {
        return [
            'Detail' => json_encode($this->message->detail()),
            'DetailType' => $this->message->event(),
            'EventBusName' => $this->eventBusName,
            'Source' => $this->message->source(),
        ];
    }

}