<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRocheUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('rl_users')->insert([
            'name' => 'Patrick de Boer',
            'email' => 'pdeboer@peakdata.ch',
            'password' => bcrypt('pdb'),
        ]);

        DB::table('rl_users')->insert([
            'name' => 'Michael Feldman',
            'email' => 'mfeldman@peakdata.ch',
            'password' => bcrypt('mf'),
        ]);

        DB::table('rl_users')->insert([
            'name' => 'Andreas Müller',
            'email' => 'andreas_roland.mueller@roche.com',
            'password' => bcrypt('am'),
        ]);

        DB::table('rl_users')->insert([
            'name' => 'Andreas Schneider',
            'email' => 'andreas.schneider.as5@roche.com',
            'password' => bcrypt('as'),
        ]);

        DB::table('rl_users')->insert([
            'name' => 'Sven Fischer',
            'email' => 'sven.fischer@roche.com',
            'password' => bcrypt('sf'),
        ]);

        DB::table('rl_users')->insert([
            'name' => 'Antonio Leo',
            'email' => 'antonio.leo@roche.com',
            'password' => bcrypt('al'),
        ]);

        DB::table('rl_users')->insert([
            'name' => 'Michael Bosshard',
            'email' => 'michael.bosshard.mb1@roche.com',
            'password' => bcrypt('mb'),
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
