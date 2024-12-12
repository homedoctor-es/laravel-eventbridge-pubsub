<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use MongoDB\Laravel\Schema\Blueprint;

class CreateEventBridgeMessagesLogTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $connection = config('eventbridge-pubsub.message_log_db_connection');
        if (!$connection) {
            throw new \Exception("You have to define a connection to the eventbridge message log collection");
        }
        Schema::connection($connection)->create('eventbridge_messages_log', function (Blueprint $collection) {
            $collection->id();
            $collection->uuid('message_id')->unique();
            $collection->string('source');
            $collection->string('event');
            $collection->json('payload');
            $collection->json('consumers')->nullable();
            $collection->dateTime('published_at');
            $collection->dateTime('expires_at')->nullable();
            $collection->timestamps();

            $collection->index(['message_id' => -1]);
            $collection->index(['source' => 1]);
            $collection->index(['event' => 1]);
            $collection->index(['published_at' => -1]);
            $collection->index(
                ['expires_at' => 1],
                options: [
                    'expireAfterSeconds' => 0,
                ]
            );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('eventbridge_messages_log');
    }

}
