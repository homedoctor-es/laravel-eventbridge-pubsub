<?php

namespace HomedoctorEs\EventBridgePubSub\Events;

use HomedoctorEs\EventBridgePubSub\Values\Message;

abstract class EventBridgeMessageEvent
{
    protected Message $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    public function message()
    {
        return $this->message;
    }

}