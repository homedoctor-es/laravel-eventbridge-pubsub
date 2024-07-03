<?php

namespace HomedoctorEs\EventBridgePubSub\Queue\Connectors;

use Aws\Sqs\SqsClient;
use Illuminate\Queue\Connectors\SqsConnector;
use Illuminate\Support\Arr;
use HomedoctorEs\EventBridgePubSub\EventBridgeSqsServiceProvider;
use HomedoctorEs\EventBridgePubSub\Queue\EventBridgeSqsQueue;

class EventBridgeSqsConnector extends SqsConnector
{
    /**
     * Establish a queue connection.
     *
     * @param  array  $config
     * @return \Illuminate\Contracts\Queue\Queue
     */
    public function connect(array $config)
    {
        $config = $this->getDefaultConfiguration($config);

        return new EventBridgeSqsQueue(
            new SqsClient(EventBridgeSqsServiceProvider::prepareConfigurationCredentials($config)),
            $config['queue'],
            Arr::get($config, 'prefix', ''),
            Arr::get($config, 'suffix', ''),
        );
    }
}
