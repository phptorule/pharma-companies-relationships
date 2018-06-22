<?php

use Illuminate\Database\Seeder;
use DB;
use App\Models\UserActivityType;

class UserActivityTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        UserActivityType::truncate();
        DB::beginTransaction();
        DB::commit();
    }
}
