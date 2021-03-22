<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClipsTable extends Migration
{
    public function up()
    {
        Schema::create('clips', function (Blueprint $table) {
            $table->string('clip_id')->unique();
            $table->string('url');
            $table->string('broadcaster_id');
            $table->string('broadcaster_name');
            $table->string('creator_id');
            $table->string('creator_name');
            $table->string('video_id');
            $table->string('game_id');
            $table->string('title');
            $table->string('view_count');
            $table->string('created_at');
            $table->string('thumbnail_url');
        });
    }

    public function down()
    {
        Schema::dropIfExists('clips');
    }
}
