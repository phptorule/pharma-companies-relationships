<?php

use App\Models\Entity;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FillRl_EntitiesTableWithDBTableNames extends Seeder
{
    /**
     * Run the database seeds.
     *
     * TODO: most likely, the `name` field must be overridden for some records
     *
     * @return void
     */
    public function run()
    {
        $tableNames = $this->getAllDBTableNames();

        if(!empty(Entity::all())) {
            Entity::truncate();
        }


        foreach ($tableNames as $table) {
            if($table == 'rl_addresses') {
                $this->addEntityRecord($table, 'ADDRESS');
            }
            elseif ($table == 'rl_products') {
                $this->addEntityRecord($table, 'PRODUCT');
            }
            elseif ($table == 'rl_people') {
                $this->addEntityRecord($table, 'PEOPLE');
            }
            elseif ($table == 'rl_feeds') {
                $this->addEntityRecord($table, 'FEED');
            }
            elseif ($table == 'rl_tags') {
                $this->addEntityRecord($table, 'TAG');
            }
            elseif ($table == 'rl_clusters') {
                $this->addEntityRecord($table, 'CLUSTER');
            }
            else {
                $this->addEntityRecord($table);
            }
        }
    }


    private function addEntityRecord($default, $name = null) {
        return Entity::create([
            'name' => $name ? $name : $default,
            'table_name' => $default
        ]);
    }


    private function getAllDBTableNames()
    {
        $values = [];

        foreach (DB::select('SHOW TABLES') as $val) {
            $values[] = $val->{'Tables_in_'.env('DB_DATABASE')};
        }

        return $values;
    }
}
