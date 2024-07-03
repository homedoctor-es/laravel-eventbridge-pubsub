<?php

namespace HomedoctorEs\EventBridgePubSub\Database\Models;

use Carbon\Carbon;
use Jenssegers\Mongodb\Eloquent\Model;
use MongoDB\BSON\UTCDateTime;

class EventBridgeMessage extends Model
{

    protected $collection = 'eventbridge_messages_log';

    protected $fillable = [
        'message_id',
        'source',
        'event',
        'payload',
        'consumers',
        'published_at',
        'expires_at'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'expires_at' => 'datetime',
        'published_at' => 'datetime',
        'updated_at' => 'datetime',
    ];


    public function getConnectionName()
    {
        return config('eventbridge-pubsub.message_log_db_connection');
    }

    public function addConsumption(string $consumer): void
    {
        $this->push(
            'consumers',
            [
                'consumer' => $consumer,
                'consumed_at' => new UTCDateTime()
            ]
        );
    }

}