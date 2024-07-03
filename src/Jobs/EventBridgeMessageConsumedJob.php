<?php

namespace HomedoctorEs\EventBridgePubSub\Jobs;

use HomedoctorEs\EventBridgePubSub\Database\Models\EventBridgeMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Jobs\Job;
use Illuminate\Support\Facades\Log;

class EventBridgeMessageConsumedJob extends EventBridgeMessageJob
{

    public function handle()
    {
        Log::debug("message consumed -> " . $this->message->messageId());
        /**
         * @var EventBridgeMessage $model
         */
        $model = EventBridgeMessage::where('message_id', $this->message->messageId())->first();
        $model?->addConsumption(config('eventbridge-pubsub.event_bridge_source'));
    }

}