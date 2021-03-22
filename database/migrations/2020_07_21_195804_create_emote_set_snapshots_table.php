<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmoteSetSnapshotsTable extends Migration
{
    public function up()
    {
        Schema::create('emote_set_snapshots', function (Blueprint $table) {
            $table->id();
            $table->string('twitch_channel_id');
            $table->string('type');
            $table->text('emote_ids')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('emote_set_snapshots');
    }
}
