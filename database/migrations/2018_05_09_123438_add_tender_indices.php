<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTenderIndices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("rl_address_tenders", function (Blueprint $table) {
            $table->index(['address_id']);
        });

        Schema::table("rl_address_tenders_budgets", function (Blueprint $table) {
            $table->index(['tender_id']);
        });

        Schema::table("rl_address_tenders_purchase", function (Blueprint $table) {
            $table->index(['tender_id']);
        });

        Schema::table("rl_address_tenders_suppliers", function (Blueprint $table) {
            $table->index(['tender_id']);
        });

        Schema::table("rl_address_tenders_purchase_products", function (Blueprint $table) {
            $table->index(['purchase_id']);
            $table->index(['product_id']);
            $table->index(['consumable_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("rl_address_tenders", function (Blueprint $table) {
            $table->dropIndex(['address_id']);
        });

        Schema::table("rl_address_tenders_budgets", function (Blueprint $table) {
            $table->dropIndex(['tender_id']);
        });

        Schema::table("rl_address_tenders_purchase", function (Blueprint $table) {
            $table->dropIndex(['tender_id']);
        });

        Schema::table("rl_address_tenders_suppliers", function (Blueprint $table) {
            $table->dropIndex(['tender_id']);
        });

        Schema::table("rl_address_tenders_purchase_products", function (Blueprint $table) {
            $table->dropIndex(['purchase_id']);
            $table->dropIndex(['product_id']);
            $table->dropIndex(['consumable_id']);
        });
    }
}
