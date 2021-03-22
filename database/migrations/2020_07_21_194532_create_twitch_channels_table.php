<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTwitchChannelsTable extends Migration
{
    public function up()
    {
        Schema::create('twitch_channels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('display_name')->nullable();
            $table->string('twitch_channel_id')->nullable();
            $table->string('current_snapshot')->nullable();
            $table->string('profile_image_url')->nullable();
            $table->string('broadcaster_type')->nullable();
            $table->text('description')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('twitch_channels');
    }
}
