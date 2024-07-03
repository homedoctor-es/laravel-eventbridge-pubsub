<?php

namespace HomedoctorEs\EventBridgePubSub\Values;

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;

class Message
{

    protected ?string $source;
    protected ?string $event;
    protected ?array $detail;

    public function __construct(array $message = [])
    {
        $this->source = $message['source'] ?? null;
        $this->event = $message['detail-type'] ?? null;
        $this->detail = $message['detail'] ?? null;
    }

    public function prepareForPublish(array $payload, string $event, string $source)
    {
        $this->cleanPayload($payload);

        $this->detail = [
            'message_id' => Str::uuid(),
            'timestamp' => Carbon::now(),
            'payload' => $payload
        ];

        $this->event = $event;
        $this->source = $source;
    }

    private function cleanPayload(&$payload): void
    {
        unset($payload['socket']);
    }

    public function source(): ?string
    {
        return $this->source;
    }

    public function event(): ?string
    {
        return $this->event;
    }

    public function detail(): ?array
    {
        return $this->detail;
    }

    public function messageId(): string
    {
        return $this->detail()['message_id'];
    }

    public function timestamp(): array
    {
        return $this->detail()['timestamp'];
    }

    public function payload(): array
    {
        return $this->detail()['payload'];
    }

    public function isValid(): bool
    {
        return $this->detail() && $this->event();
    }

}