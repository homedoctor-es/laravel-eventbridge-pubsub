<?php

namespace HomedoctorEs\EventBridgePubSub\Queue\Connectors;

use Aws\Sqs\SqsClient;
use HomedoctorEs\EventBridgePubSub\EventBridgePubSubServiceProvider;
use HomedoctorEs\EventBridgePubSub\Queue\EventBridgeSqsQueue;
use Illuminate\Queue\Connectors\SqsConnector;
use Illuminate\Support\Arr;

class EventBridgeSqsConnector extends SqsConnector
{

    /**
     * Establish a queue connection.
     *
     * @param array $config
     * @return \Illuminate\Contracts\Queue\Queue
     */
    public function connect(array $config)
    {
        $config = $this->getDefaultConfiguration($config);

        return new EventBridgeSqsQueue(
            new SqsClient(EventBridgePubSubServiceProvider::prepareConfigurationCredentials($config)),
            $config['queue'],
            Arr::get($config, 'prefix', ''),
            Arr::get($config, 'suffix', ''),
        );
    }

}
