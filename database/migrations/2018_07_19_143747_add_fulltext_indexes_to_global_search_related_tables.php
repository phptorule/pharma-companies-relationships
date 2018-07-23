<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFulltextIndexesToGlobalSearchRelatedTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //People table
        DB::statement('ALTER TABLE rl_people ADD FULLTEXT INDEX fulltext_name (name)');
        DB::statement('ALTER TABLE rl_people ADD FULLTEXT INDEX fulltext_role (role)');
        DB::statement('ALTER TABLE rl_people ADD FULLTEXT INDEX fulltext_description (description)');

        //Addresses table
        DB::statement('ALTER TABLE rl_addresses ADD FULLTEXT INDEX fulltext_name (name)');
        DB::statement('ALTER TABLE rl_addresses ADD FULLTEXT INDEX fulltext_address (address)');

        //Products table
        DB::statement('ALTER TABLE rl_products ADD FULLTEXT INDEX fulltext_name (name)');
        DB::statement('ALTER TABLE rl_products ADD FULLTEXT INDEX fulltext_company (company)');

        //Clusters table
        DB::statement('ALTER TABLE rl_clusters ADD FULLTEXT INDEX fulltext_name (name)');

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE rl_people DROP INDEX fulltext_name');
        DB::statement('ALTER TABLE rl_people DROP INDEX fulltext_role');
        DB::statement('ALTER TABLE rl_people DROP INDEX fulltext_description');


        DB::statement('ALTER TABLE rl_addresses DROP INDEX fulltext_name');
        DB::statement('ALTER TABLE rl_addresses DROP INDEX fulltext_address');


        DB::statement('ALTER TABLE rl_products DROP INDEX fulltext_name');
        DB::statement('ALTER TABLE rl_products DROP INDEX fulltext_company');


        DB::statement('ALTER TABLE rl_clusters DROP INDEX fulltext_name');
    }
}
