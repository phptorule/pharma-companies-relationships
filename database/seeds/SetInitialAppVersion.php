<?php

use App\Models\AppVersion;
use Illuminate\Database\Seeder;

class SetInitialAppVersion extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        AppVersion::create([
            'main' => 1,
            'major' => 0,
            'minor' => 0
        ]);
    }
}
