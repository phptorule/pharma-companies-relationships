<?php

use Illuminate\Database\Seeder;
use App\Models\User;

class SetAdminUsers extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $u1 = User::where('email', 'pdeboer@peakdata.ch')->first();
        $u2 = User::where('email', 'mfeldman@peakdata.ch')->first();
        $u1->role = 'admin';
        $u2->role = 'admin';
        $u1->save();
        $u2->save();
    }
}
