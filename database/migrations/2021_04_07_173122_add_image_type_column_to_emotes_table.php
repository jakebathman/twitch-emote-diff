<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddImageTypeColumnToEmotesTable extends Migration
{
    public function up()
    {
        Schema::table('emotes', function (Blueprint $table) {
            $table->string('image_type')->nullable()->default('png')->after('plan_id');
        });
    }

    public function down()
    {
        Schema::table('emotes', function (Blueprint $table) {
            $table->dropColumn('image_type');
        });
    }
}
