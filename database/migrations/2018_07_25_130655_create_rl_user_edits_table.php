<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRlUserEditsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rl_user_edits', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->integer('entity_id')->unsigned();
            $table->integer('entity_row_key')->unsigned()->nullable();
            $table->string('field');
            $table->string('new_value')->nullable();
            $table->string('old_value')->nullable();
            $table->string('url_formatter')->nullable();
            $table->string('batch_uuid');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rl_user_edits');
    }
}
