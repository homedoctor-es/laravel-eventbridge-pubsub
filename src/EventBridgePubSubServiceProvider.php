<?php

namespace HomedoctorEs\EventBridgePubSub;

use Aws\EventBridge\EventBridgeClient;
use HomedoctorEs\EventBridgePubSub\Broadcasting\Broadcasters\EventBridgeBroadcaster;
use HomedoctorEs\EventBridgePubSub\Queue\Connectors\EventBridgeSqsConnector;
use Illuminate\Contracts\Broadcasting\Factory as BroadcastManager;
use Illuminate\Contracts\Container\Container;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Queue\QueueManager;
use Illuminate\Support\Arr;

class EventBridgePubsubServiceProvider extends ServiceProvider
{

    /**
     * @inheritDoc
     */
    public function register()
    {
        parent::register();

        $this->registerEventBridgeBroadcaster();
        $this->registerEventBridgeSqsQueueConnector();
    }


    /**
     * Register the EventBridge broadcaster for the Broadcast components.
     *
     * @return void
     */
    protected function registerEventBridgeBroadcaster()
    {
        $this->app->resolving(BroadcastManager::class, function (BroadcastManager $manager) {
            $manager->extend('eventbridge', function (Container $app, array $config) {
                return $this->createEventBridgeDriver($config);
            });
        });
    }

    /**
     * Create an instance of the EventBridge driver for broadcasting.
     *
     * @param  array  $config
     * @return \Illuminate\Contracts\Broadcasting\Broadcaster
     */
    public function createEventBridgeDriver(array $config)
    {
        $config = self::prepareConfigurationCredentials($config);
        return new EventBridgeBroadcaster(
            new EventBridgeClient(array_merge($config, ['version' => '2015-10-07'])),
            $config['source'] ?? '',
        );
    }

    /**
     * Register the SQS EventBridge connector for the Queue components.
     *
     * @return void
     */
    protected function registerEventBridgeSqsQueueConnector()
    {
        $this->app->resolving('queue', function (QueueManager $manager) {
            $manager->extend('eventbridge-sqs', function () {
                return new EventBridgeSqsConnector;
            });
        });
    }


    /**
     * Parse and prepare the AWS credentials needed by the AWS SDK library from the config.
     *
     * @param array $config
     * @return array
     */
    public static function prepareConfigurationCredentials(array $config): array
    {
        if (static::configHasCredentials($config)) {
            $config['credentials'] = Arr::only($config, ['key', 'secret', 'token']);
        }

        return $config;
    }

    /**
     * Make sure some AWS credentials were provided to the configuration array.
     *
     * @return bool
     */
    private static function configHasCredentials(array $config): bool
    {
        return Arr::has($config, ['key', 'secret'])
            && Arr::get($config, 'key')
            && Arr::get($config, 'secret');
    }

}