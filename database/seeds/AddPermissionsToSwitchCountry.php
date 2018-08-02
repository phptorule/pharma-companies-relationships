<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class AddPermissionsToSwitchCountry extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        if(!Permission::where('name', 'switch-country')->count()) {

            $perm = new Permission();
            $perm->name         = 'switch-country';
            $perm->display_name = 'Switch Country';
            $perm->description  = 'User can switch his default_country';
            $perm->save();


            $owner = Role::where('name', 'owner')->first();
            $admin = Role::where('name', 'admin')->first();

            if(!$owner->has)

            $owner->attachPermissions([$perm]);
            $admin->attachPermissions([$perm]);
        }
    }
}
