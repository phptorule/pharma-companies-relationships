<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class AddOnlyOneCountryUserRole extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /**
         * Create `ch-user` role
         * and assign permission to it
         */
        if (!Role::where('name', 'ch-user')->count()) {
            $chUser = new Role();
            $chUser->name         = 'ch-user';
            $chUser->display_name = 'User of Switzerland';
            $chUser->description  = 'This user is able to use only Switzerland version of Labscape';
            $chUser->save();

            if(!$chUser->hasPermission('access-ch')) {
                $chUser->attachPermission(Permission::where('name', 'access-ch')->first());
            }
        }

        /**
         * Create `ru-user` role
         * and assign permission to it
         */
        if (!Role::where('name', 'ru-user')->count()) {
            $ruUser = new Role();
            $ruUser->name         = 'ru-user';
            $ruUser->display_name = 'User of Russia';
            $ruUser->description  = 'This user is able to use only Russian version of Labscape';
            $ruUser->save();

            if(!$ruUser->hasPermission('access-ru')) {
                $ruUser->attachPermission(Permission::where('name', 'access-ru')->first());
            }
        }
    }
}
