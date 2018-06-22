<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrackingTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rl_user_activity', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->timestamp('created_at')->default(\DB::raw('CURRENT_TIMESTAMP'));
            $table->string('url', 1024);
            $table->integer('activity_type_id')->unsigned();
            $table->string('user_agent', 2048);
            $table->string('ip', 255);
            $table->string('payload', 1024);
        });

        Schema::create('rl_user_activity_type', function (Blueprint $table) {
            $table->increments('id');
            $table->string('component_name', 255);
            $table->string('component_action', 255);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rl_user_activity');
        Schema::dropIfExists('rl_user_activity_type');
    }
}
