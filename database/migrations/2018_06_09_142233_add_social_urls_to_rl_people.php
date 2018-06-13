<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSocialUrlsToRlPeople extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rl_people', function (Blueprint $table) {
            $table->text('telegram')->nullable()->after('linkedin_url');
            $table->text('instagram')->nullable()->after('linkedin_url');
            $table->text('facebook')->nullable()->after('linkedin_url');
            $table->text('twitter')->nullable()->after('linkedin_url');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rl_people', function (Blueprint $table) {
            $table->dropColumn(['telegram', 'instagram', 'facebook', 'twitter']);
        });
    }
}
