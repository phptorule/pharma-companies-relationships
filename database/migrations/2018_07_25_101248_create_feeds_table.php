<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFeedsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rl_feeds', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('feed_type_id')->unsigned()->nullable();
            $table->dateTime('actual_date')->nullable();
            $table->dateTime('found_date')->nullable();
            $table->text('img')->nullable();
            $table->timestamps();
        });

        Schema::create('rl_feed_subjects', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('feed_id')->unsigned()->nullable();
            $table->integer('person_id')->unsigned()->nullable();
            $table->integer('company_id')->unsigned()->nullable();
            $table->enum('subject_role', ['ORIGIN', 'TARGET'])->nullable();
            $table->integer('order')->default(100);
            $table->timestamps();
        });

        Schema::create('rl_feed_objects', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('feed_id')->unsigned()->nullable();
            $table->integer('entity_id')->unsigned()->nullable();
            $table->integer('object_id')->unsigned()->nullable();
            $table->integer('order')->default(100);
            $table->timestamps();
        });

        Schema::create('rl_feed_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('rl_feed_opinions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('feed_id')->unsigned()->nullable();
            $table->integer('user_id')->unsigned()->nullable();
            $table->double('estimated_attractiveness');
            $table->integer('user_responses')->default(0);
            $table->boolean('saved')->default(false);
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
        Schema::dropIfExists('rl_feeds');
        Schema::dropIfExists('rl_feed_subjects');
        Schema::dropIfExists('rl_feed_objects');
        Schema::dropIfExists('rl_feed_types');
        Schema::dropIfExists('rl_feed_opinions');
    }
}
