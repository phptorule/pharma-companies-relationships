<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
         $this->call([
             UsersTableSeeder::class,
             ChangeZeroIdAtTablePeopleTypes::class,
             ChangeZeroIdAtTablePeopleTypes::class,
             add_NEXT_DEPLOYMENT_AT_configuration::class,
             add_NEXT_DEPLOYMENT_AT_website_notification::class,
             CreateBaseRolesAndPermissions::class,
             CreateBasePermissions::class,
             AddPermissionsToSwitchCountry::class,
             AddOnlyOneCountryUserRole::class
         ]);
    }
}
