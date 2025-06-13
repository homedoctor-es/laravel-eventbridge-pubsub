<?php

namespace HomedoctorEs\EventBridgePubSub\Values;

use Carbon\CarbonInterface;
use DateTimeInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;

class Message
{

    protected ?string $source;
    protected ?string $event;
    protected ?array $detail;

    private const TIMESTAMP_FORMAT = 'Y-m-d H:i:s.u';

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

    public function timestamp(): ?Carbon
    {
        $timestamp = $this->detail()['timestamp'];

        if ($timestamp instanceof CarbonInterface) {
            return $timestamp;
        }

        if ($timestamp instanceof DateTimeInterface) {
            return Carbon::parse($timestamp, $timestamp->getTimezone());
        }

        if (is_array($timestamp)) {
            $date = null;
            if (isset($timestamp['date'])) {
                $date = Carbon::createFromFormat(self::TIMESTAMP_FORMAT, $timestamp['date']);
            }
            if ($date && isset($timestamp['timezone'])) {
                $date->setTimezone($timestamp['timezone']);
            }
            return $date;
        }

        if (is_string($timestamp)) {
            return Carbon::createFromFormat(self::TIMESTAMP_FORMAT, $timestamp);
        }

        return null;
    }

    public function payload(): array
    {
        return $this->detail()['payload'];
    }

    public function isValid(): bool
    {
        return $this->detail() && $this->event();
    }

    public function toModelAttributes()
    {
        return [
            'message_id' => $this->messageId(),
            'published_at' => $this->timestamp(),
            'source' => $this->source(),
            'payload' => $this->payload(),
            'expires_at' => now()->addMinutes(config('eventbridge-pubsub.message_log_db_expiration_minutes')),
            'event' => $this->event()
        ];
    }

}