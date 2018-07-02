<?php

use Illuminate\Database\Seeder;
use App\Models\AddressConnectionType;

class AddressConnectionTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $addressConnectionType = new AddressConnectionType();
        $addressConnectionType->name = 'Manual';
        $addressConnectionType->save();
    }
}
