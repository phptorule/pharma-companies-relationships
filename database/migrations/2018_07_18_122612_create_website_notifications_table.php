<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWebsiteNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rl_website_notifications', function (Blueprint $table) {
            $table->increments('id');
            $table->string('key');
            $table->string('title', 1024)->nullable();
            $table->text('body')->nullable();
            $table->timestamp('expired_at' )->nullable();
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
        Schema::dropIfExists('rl_website_notifications');
    }
}
