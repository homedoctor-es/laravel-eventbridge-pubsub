<?php

namespace HomedoctorEs\EventBridgePubSub\Queue;

use HomedoctorEs\EventBridgePubSub\Queue\Jobs\EventBridgeEventDispatcherJob;
use HomedoctorEs\EventBridgePubSub\Queue\Jobs\SnsEventDispatcherJob;
use Illuminate\Queue\SqsQueue;
use Illuminate\Support\Facades\Log;

class EventBridgeSqsQueue extends SqsQueue
{

    /**
     * @inheritDoc
     */
    public function pushRaw($payload, $queue = null, array $options = [])
    {
        if ($this->container->bound('log')) {
            Log::error('Unsupported: sqs-sns queue driver is read-only');
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function later($delay, $job, $data = '', $queue = null)
    {
        if ($this->container->bound('log')) {
            Log::error('Unsupported: sqs-sns queue driver is read-only');
        }

        return null;
    }

    /**
     * Pop the next job off of the queue.
     *
     * @param string $queue
     * @return \Illuminate\Contracts\Queue\Job|null
     */
    public function pop($queue = null)
    {
        $queue = $this->getQueue($queue);
        $response = $this->sqs->receiveMessage([
            'QueueUrl' => $queue,
            'AttributeNames' => ['ApproximateReceiveCount'],
        ]);

        if (!is_null($response['Messages']) && count($response['Messages']) > 0) {
            return new EventBridgeEventDispatcherJob(
                $this->container, $this->sqs, $response['Messages'][0],
                $this->connectionName, $queue
            );
        }
    }

}
