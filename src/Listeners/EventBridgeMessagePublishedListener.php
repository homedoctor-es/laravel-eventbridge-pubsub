<?php

namespace HomedoctorEs\EventBridgePubSub\Listeners;

use HomedoctorEs\EventBridgePubSub\Events\EventBridgeMessagePublished;
use HomedoctorEs\EventBridgePubSub\Jobs\EventBridgeMessagePublishedJob;
use Illuminate\Support\Facades\Log;

class EventBridgeMessagePublishedListener
{

    public function handle(EventBridgeMessagePublished $event)
    {
        dispatch(new EventBridgeMessagePublishedJob($event->message()));
    }
}