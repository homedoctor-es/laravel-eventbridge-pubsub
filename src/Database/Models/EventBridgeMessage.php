<?php

namespace HomedoctorEs\EventBridgePubSub\Database\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\BSON\UTCDateTime;

class EventBridgeMessage extends Model
{

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

    public function getTable()
    {
        return config('eventbridge-pubsub.message_log_db_collection') ?? parent::getTable();
    }

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