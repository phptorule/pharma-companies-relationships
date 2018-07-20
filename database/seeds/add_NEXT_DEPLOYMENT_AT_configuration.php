<?php

use App\Models\Configuration;
use Illuminate\Database\Seeder;

class add_NEXT_DEPLOYMENT_AT_configuration extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Configuration::create([
            'key' => 'NEXT_DEPLOYMENT_AT'
        ]);
    }
}
