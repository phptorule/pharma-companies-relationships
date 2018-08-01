<?php

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;

class CreateBaseRolesAndPermissions extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        if (!Role::where('name', 'owner')->count()) {

            $owner = new Role();
            $owner->name         = 'owner';
            $owner->display_name = 'Project Owner';
            $owner->description  = 'Absolute power!';
            $owner->save();

            $users = User::whereIn('email', [
                'pdeboer@peakdata.ch',
                'mfeldman@peakdata.ch',
                'admin@gmail.com'
            ])->get();

            foreach ($users as $user) {
                if (!$user->hasRole('owner')) {
                    $user->attachRole($owner);
                }
            }
        }


        if (!Role::where('name', 'admin')->count()) {
            $admin = new Role();
            $admin->name         = 'admin';
            $admin->display_name = 'User Administrator';
            $admin->description  = 'User is allowed to manage and edit other users';
            $admin->save();
        }
    }
}
