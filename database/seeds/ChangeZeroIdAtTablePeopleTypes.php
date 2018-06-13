<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChangeZeroIdAtTablePeopleTypes extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('rl_people_types')
            ->where('id',0)
            ->update(['id'=>1]);

        DB::table('rl_people')
            ->where('type_id',0)
            ->update(['type_id'=>1]);
    }
}
