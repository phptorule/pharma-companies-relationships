<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRlEntitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        if(Schema::hasTable('rl_entities')) {
            return;
        }

        Schema::create('rl_entities', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->string('table_name');
            $table->text('field_mapping')->nullable();
            $table->timestamps();
        });

        Artisan::call('db:seed', [
            '--class' => FillRl_EntitiesTableWithDBTableNames::class
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rl_entities');
    }
}
