<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUnitsColumnAtRlAddressTendersPurchaseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasColumn('rl_address_tenders_purchase', 'units')) {
            return;
        }

        Schema::table('rl_address_tenders_purchase', function (Blueprint $table) {
            $table->string('units', 500)->nullable()->after('tender_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if(!Schema::hasColumn('rl_address_tenders_purchase', 'units')) {
            return;
        }

        Schema::table('rl_address_tenders_purchase', function (Blueprint $table) {
            $table->dropColumn('units');
        });
    }
}
