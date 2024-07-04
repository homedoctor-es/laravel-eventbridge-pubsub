# Laravel EventBridge Broadcaster

[![Latest Version on Packagist](https://img.shields.io/packagist/v/homedoctor-es/laravel-eventbridge-pubsub.svg?style=flat-square)](https://packagist.org/packages/homedoctor-es/laravel-eventbridge-pubsub)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Total Downloads](https://img.shields.io/packagist/dt/homedoctor-es/laravel-eventbridge-pubsub.svg?style=flat-square)](https://packagist.org/packages/homedoctor-es/laravel-eventbridge-pubsub)

**The Pub**

Similar to [Pusher](https://laravel.com/docs/broadcasting#pusher-channels), this package provides [Laravel Broadcasting](https://laravel.com/docs/broadcasting) driver for [AWS EventBridge](https://aws.amazon.com/eventbridge/) in order to publish server-side events.

We understand [Broadcasting](https://laravel.com/docs/broadcasting) is usually used to "broadcast" your server-side Laravel [Events](https://laravel.com/docs/events) over a WebSocket connection to your client-side JavaScript application. However, we believe this approach of leveraging broadcasting makes sense for a Pub/Sub architecture where an application would like to broadcast a server-side event to the outside world about something that just happened.

In this context, "channels" can be assimilated to "topics" when using the SNS driver and "event buses" when using the EventBridge driver.

**The Sub**

We simply have to listen to these messages pushed to an SQS queue and act upon them. The only difference here is that we don't use the default Laravel SQS driver as the messages pushed are not following Laravel's classic JSON payload for queued Jobs/Events pushed from a Laravel application. The messages from EventBridge are simpler.


For this purpose, we're using an architecture like this:
1. "n" producers who publish events in EventBridge
2. Each event is published in one or more SNS topic.
3. Each one of these topics can be consumed by one or more SQS queues.
4. Each one of these queues correspond to one app.

## Installation

You can install the package on a Laravel 8+ application via composer:

```bash
composer require homedoctor-es/laravel-eventbridge-pubsub
```

Then, add HomedoctorEs\EventBridgePubSub\EventBridgePubSubServiceProvider::class to load the driver in config/app.php file in order to be able to use the package.

Once done this last step, you can publish the package and a config file called eventbridge-pubsub.php will be added to you config path

```php
return [
    'messages_log_active' => false, //This param will allow you to log your messages
    'message_log_db_connection' => null, //This params indicate if you will store this logs in an existent MongoDB connection(if the table does not exists in the connection, there is a migration to create the table)
    'message_log_db_expiration_minutes' => 43200, // TTL to expires the documents created in the database, 30 days default
    'event_bridge_source' => null, // Mandatory field to indicates the name of the app which will consume and produce events
];
```

## Publishing / Broadcasting

### Configuration

You will need to add the following connection and configure your AWS credentials in the `config/broadcasting.php` configuration file:

```php
'connections' => [

    'eventbridge' => [ //The connection name will be used as default eventbus
        'driver' => 'eventbridge',
        'region' => env('AWS_DEFAULT_REGION'),
        'key' => env('AWS_ACCESS_KEY_ID'),
        'endpoint' => env('AWS_URL'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'source' => env('AWS_EVENTBRIDGE_SOURCE'),
    ],
    // ...
],
```

Make sure to define your [environment variables](https://laravel.com/docs/configuration#environment-configuration) accordingly:

```dotenv
# both drivers require:
AWS_DEFAULT_REGION=you-region
AWS_ACCESS_KEY_ID=your-aws-key
AWS_SECRET_ACCESS_KEY=your-aws-secret

# EventBridge driver only:
AWS_EVENTBRIDGE_SOURCE=com.your-app-name
# This env var can be called as you prefer, but make sure that is the same that you will be set at "event_bridge_source" config property  
```



Next, if you want to use the `eventbridge` broadcast driver as your default driver when broadcasting in your `.env` file:

```php
BROADCAST_DRIVER=eventbridge
```

**Remember** that you can define the connection at the Event level if you ever need to be able to use [two drivers concurrently](https://github.com/laravel/framework/pull/38086).
```php
use InteractsWithBroadcasting;

public function __construct()
{
    $this->broadcastVia('eventbridge');
}
```

### Usage

Simply follow the default way of broadcasting Laravel events, explained in the [official documentation](https://laravel.com/docs/broadcasting#defining-broadcast-events).

In a similar way, you will have to make sure you're implementing the `Illuminate\Contracts\Broadcasting\ShouldBroadcast` interface and define which channel you'd like to broadcast on.

```php
use App\Models\Order;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\InteractsWithBroadcasting;
use Illuminate\Queue\SerializesModels;

class OrderShipped implements ShouldBroadcast
{
    use SerializesModels;

    /**
     * The order that was shipped.
     *
     * @var \App\Models\Order
     */
    public $order;

    /**
     * Create a new event instance.
     *
     * @param  \App\Models\Order  $order
     * @return void
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
        $this->broadcastVia('eventbridge'); // If is not the default broadcaster driver
    }

    /**
     * Get the topics that model events should broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return ['orders']; // This is the Event bus name
    }
}
```

#### Broadcast Data

By default, the package will publish the default Laravel payload which is already used when broadcasting an Event. Once published, its JSON representation could look like this:

```json
{
    "order": {
        "id": 1,
        "name": "Some Goods",
        "total": 123456,
        "created_at": "2021-06-29T13:21:36.000000Z",
        "updated_at": "2021-06-29T13:21:36.000000Z"
    },
    "connection": null,
    "queue": null
}

```

Using the `broadcastWith` method, you will be able to define exactly what kind of payload gets published.

```php
/**
 * Get and format the data to broadcast.
 *
 * @return array
 */
public function broadcastWith()
{
    return [
        'action' => 'parcel_handled',
        'data' => [
            'order-id' => $this->order->id,
            'order-total' => $this->order->total,
        ],
    ];
}
```

Now, when the event is being triggered, it will behave like a standard Laravel event, which means other listeners can listen to it, as usual, but it will also broadcast to the Topic defined by the `broadcastOn` method using the payload defined by the `broadcastWith` method.

#### Broadcast Name / Subject

In a Pub/Sub context, it can be handy to specify a `Subject` on each notification which broadcast to SNS. This can be an easy way to configure a Listeners for each specific kind of subject you can receive and process later on within queues.

By default, the package will use the standard [Laravel broadcast name](https://laravel.com/docs/broadcasting#broadcast-name) in order to define the `Subject` of the notification sent. Feel free to customize it as you wish.

```php
/**
 * The event's broadcast name/subject.
 *
 * @return string
 */
public function broadcastAs()
{
    return "orders.{$this->action}";
}
```

#### Model Broadcasting

If you're familiar with [Model Broadcasting](https://laravel.com/docs/broadcasting#model-broadcasting), you already know that Eloquent models dispatch several events during their lifecycle and broadcast them accordingly.

In the context of model broadcasting, only the following model events can be broadcasted:

- `created`
- `updated`
- `deleted`
- `trashed` _if soft delete is enabled_
- `restored` _if soft delete is enabled_

In order to broadcast the model events, you need to use the `Illuminate\Database\Eloquent\BroadcastsEvents` trait on your Model and follow the official [documentation]((https://laravel.com/docs/broadcasting#model-broadcasting)).

You can use `broadcastOn()`, `broadcastWith()` and `broadcastAs()` methods on your model in order to customize the Topic names, the payload and the Subject respectively.

> **Note:** Model Broadcasting is **only available from Laravel 8.x**.

## Credits

- [laravel-aws-pubsub](https://github.com/Pod-Point/laravel-aws-pubsub) for some inspiration

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.