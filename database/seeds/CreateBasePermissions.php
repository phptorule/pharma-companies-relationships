<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class CreateBasePermissions extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $accessRU = new Permission();
        $accessRU->name         = 'access-ru';
        $accessRU->display_name = 'Access RU';
        $accessRU->description  = 'Access RU version of the app';
        $accessRU->save();


        $accessCH = new Permission();
        $accessCH->name         = 'access-ch';
        $accessCH->display_name = 'Access CH';
        $accessCH->description  = 'Access CH version of the app';
        $accessCH->save();


        $owner = Role::where('name', 'owner')->first();

        $owner->attachPermissions([$accessRU->id, $accessCH->id]);


    }
}
