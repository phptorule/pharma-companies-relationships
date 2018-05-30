<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class People extends Model
{

    protected $table = 'rl_people';


    function addresses()
    {
        return $this->belongsToMany(Address::class, 'rl_address_people', 'person_id','address_id');
    }


    function careers()
    {
        return $this->hasMany(Career::class, 'person_id');
    }


    function publications()
    {
        return $this->belongsToMany(Publication::class, 'rl_people_publications', 'person_id', 'publication_id');
    }


    function relationships()
    {
        return $this->belongsToMany(People::class, 'rl_address_connections', 'from_person_id', 'to_person_id')
            ->withPivot('edge_type');
    }


    static function getLabWorkers($related_labs_ids)
    {
        // get workers of each lab
        $sqlQuery = "SELECT rl.id, rl.name, ap.address_id, pt.name as 'workerType' FROM rl_people rl  
                JOIN rl_address_people ap ON ap.person_id = rl.id JOIN rl_people_types pt ON rl.type_id = pt.id  
                WHERE ap.address_id IN (" . $related_labs_ids . ")";

        return DB::select(DB::raw($sqlQuery));
    }


    static function getPersonInfo($personId)
    {
        $sqlQuery = "SELECT p.id, p.name, ap.address_id FROM rl_address_people ap JOIN rl_people p ON ap.person_id = p.id WHERE ap.person_id = " . $personId;

        return DB::select(DB::raw($sqlQuery));
    }


    static function getRelatedLabsPeople($related_labs_ids)
    {
        $sqlQuery = "SELECT p.id, p.name, ap.address_id FROM rl_address_people ap JOIN rl_people p ON ap.person_id = p.id WHERE ap.address_id IN (" . $related_labs_ids . ")";

        return DB::select(DB::raw($sqlQuery));
    }


    static function createRelatedPeopleIds($related_people){
        $first = true;
        $related_people_ids = "";
        foreach ($related_people as $p){
            if ($first){
                $first = false;
            }else{
                $related_people_ids = $related_people_ids . ",";
            }
            $related_people_ids = $related_people_ids . $p->id;
        }
        return $related_people_ids;
    }


    static function getPeopleRelationships($related_people_ids)
    {
        $sqlQuery = "SELECT ac.from_person_id, ac.to_person_id, ac.edge_weight, act.id as 'connection_type' FROM rl_address_connections ac LEFT JOIN rl_address_connection_types act on ac.edge_type = act.id WHERE ac.from_person_id IN (" . $related_people_ids . ") AND ac.to_person_id IN (" . $related_people_ids . ") ";

        return DB::select(DB::raw($sqlQuery));
    }

}
