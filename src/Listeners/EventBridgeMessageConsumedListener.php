<?php

namespace HomedoctorEs\EventBridgePubSub\Listeners;

use HomedoctorEs\EventBridgePubSub\Events\EventBridgeMessageConsumed;
use HomedoctorEs\EventBridgePubSub\Jobs\EventBridgeMessageConsumedJob;
use Illuminate\Support\Facades\Log;

class EventBridgeMessageConsumedListener
{

    public function handle(EventBridgeMessageConsumed $event)
    {
        if (!config('eventbridge-pubsub.messages_log_active')) {
            return;
        }
        dispatch(new EventBridgeMessageConsumedJob($event->message()));
    }

}