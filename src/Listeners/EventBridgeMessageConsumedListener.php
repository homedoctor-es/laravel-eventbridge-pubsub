<?php

namespace HomedoctorEs\EventBridgePubSub\Listeners;

use HomedoctorEs\EventBridgePubSub\Events\EventBridgeMessageConsumed;
use HomedoctorEs\EventBridgePubSub\Events\EventBridgeMessagePublished;
use HomedoctorEs\EventBridgePubSub\Jobs\EventBridgeMessageConsumedJob;
use Illuminate\Support\Facades\Log;

class EventBridgeMessageConsumedListener
{

    public function handle(EventBridgeMessageConsumed $event)
    {
        dispatch(new EventBridgeMessageConsumedJob($event->message()));
    }
}