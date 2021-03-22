<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSnapshotChangesTable extends Migration
{
    public function up()
    {
        Schema::create('snapshot_changes', function (Blueprint $table) {
            $table->id();
            $table->string('twitch_channel_id');
            $table->string('snapshot_id');
            $table->text('emote_ids_added')->nullable();
            $table->text('emote_ids_removed')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('snapshot_changes');
    }
}
