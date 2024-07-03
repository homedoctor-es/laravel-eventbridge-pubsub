<?php

namespace HomedoctorEs\EventBridgePubSub\Broadcasting\Broadcasters;

use Aws\EventBridge\EventBridgeClient;
use HomedoctorEs\EventBridgePubSub\Values\EventBridgeEvent;
use HomedoctorEs\EventBridgePubSub\Values\EventBridgeEvents;
use HomedoctorEs\EventBridgePubSub\Values\Message;
use Illuminate\Broadcasting\Broadcasters\Broadcaster;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;

class EventBridgeBroadcaster extends Broadcaster
{

    /**
     * @var EventBridgeClient
     */
    protected $eventBridgeClient;

    /**
     * @var string
     */
    protected $source;

    /**
     * EventBridgeBroadcaster constructor.
     *
     * @param EventBridgeClient $eventBridgeClient
     * @param string $source
     */
    public function __construct(EventBridgeClient $eventBridgeClient, string $source = '')
    {
        $this->eventBridgeClient = $eventBridgeClient;
        $this->source = $source;
    }

    /**
     * @inheritDoc
     */
    public function auth($request)
    {
        //
    }

    /**
     * @inheritDoc
     */
    public function validAuthenticationResponse($request, $result)
    {
        //
    }

    /**
     * @inheritDoc
     */
    public function broadcast(array $channels, $event, array $payload = [])
    {
        $events = $this->mapToEventBridgeEntries($channels, $event, $payload);
        $result = $this->eventBridgeClient->putEvents([
            'Entries' => $events->toArray(),
        ]);

        if ($this->failedToBroadcast($result)) {
            Log::error('Failed to send events to EventBridge', [
                'errors' => collect($result->get('Entries'))->filter(function (array $entry) {
                    return Arr::hasAny($entry, ['ErrorCode', 'ErrorMessage']);
                })->toArray(),
            ]);
        }
    }

    /**
     * @param array $channels
     * @param string $event
     * @param array $payload
     * @return array
     */
    protected function mapToEventBridgeEntries(array $channels, string $event, array $payload): EventBridgeEvents
    {
        $events = new EventBridgeEvents();
        collect($channels)
            ->each(function ($channel) use ($event, $payload, &$events) {
                $message = new Message();
                $message->prepareForPublish($payload, $event, $this->source);
                $events->addEvent(new EventBridgeEvent($message, $channel));
            })
            ->all();
        return $events;
    }

    protected function failedToBroadcast(?\Aws\Result $result): bool
    {
        return $result
            && $result->hasKey('FailedEntryCount')
            && $result->get('FailedEntryCount') > 0;
    }

}
