<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmotesTable extends Migration
{
    public function up()
    {
        Schema::create('emotes', function (Blueprint $table) {
            $table->id();
            $table->string('emote_id');
            $table->string('code');
            $table->string('type');
            $table->string('plan_id');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('emotes');
    }
}
