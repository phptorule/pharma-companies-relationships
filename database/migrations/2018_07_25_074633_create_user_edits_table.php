<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserEditsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $allTableNamesInArr = $this->getAllDBTableNames();

        Schema::create('rl_user_edits', function (Blueprint $table) use ($allTableNamesInArr) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->enum('entity', $allTableNamesInArr);
            $table->integer('entity_row_key')->unsigned()->nullable();
            $table->string('field');
            $table->string('new_value')->nullable();
            $table->string('old_value')->nullable();
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


    private function getAllDBTableNames()
    {
        $values = [];

        foreach (DB::select('SHOW TABLES') as $val) {
            $values[] = $val->{'Tables_in_'.env('DB_DATABASE')};
        }

        return $values;
    }

}
