<?php

namespace HomedoctorEs\EventBridgePubSub\Jobs;

use HomedoctorEs\EventBridgePubSub\Events\EventBridgeMessageConsumed;
use HomedoctorEs\EventBridgePubSub\Events\EventBridgeMessagePublished;
use HomedoctorEs\EventBridgePubSub\Values\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Jobs\Job;
use Illuminate\Support\Facades\Log;

class EventBridgeMessageJob
{
    use Dispatchable, InteractsWithQueue, Queueable;

    protected Message $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

}