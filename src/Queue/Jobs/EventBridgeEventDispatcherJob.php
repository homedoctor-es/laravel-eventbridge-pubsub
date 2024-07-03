<?php

namespace HomedoctorEs\EventBridgePubSub\Queue\Jobs;

use HomedoctorEs\EventBridgePubSub\Values\Message;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Queue\Job as JobContract;
use Illuminate\Queue\Jobs\SqsJob;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class EventBridgeEventDispatcherJob extends SqsJob implements JobContract
{

    protected Message $message;

    /**
     * @inheritDoc
     */
    public function fire()
    {
        $this->message = new Message($this->payload());
        if (!$this->message->isValid()) {
            if ($this->container->bound('log')) {
                Log::error('SqsSnsQueue: Invalid payload. ' .
                    'Make sure your JSON is a valid JSON object and has fields detail and detail-type.', $this->job);
            }

            $this->release();

            return;
        }

        if ($eventName = $this->message->event()) {
            $this->resolve(Dispatcher::class)->dispatch($eventName, [
                $this->message
            ]);
        }

        $this->delete();
    }

    /**
     * @inheritDoc
     */
    protected function failed($e)
    {
        dump($e);
    }

    public function resolveName()
    {
        return $this->getName();
    }

    public function getName()
    {
        // Don't use Message object because at this point is not initialized
        return $this->payload()['detail-type'];
    }

}
