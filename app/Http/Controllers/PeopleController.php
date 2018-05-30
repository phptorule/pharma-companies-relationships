<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\ConnectionTypes;
use App\Models\People;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PeopleController extends Controller
{


    function show(People $person)
    {
        $person->load(['careers' => function($q){
            return $q->orderBy('enddate', 'desc');
        }]);
        $person->load(['addresses' => function($q){
            return $q->orderBy('id', 'desc');
        }]);
        $person->load('publications','relationships');
        return response()->json($person);
    }


    function getConnectionTypes()
    {
        return response()->json(ConnectionTypes::all());
    }


    function getPersonRelationships(People $person)
    {
        $relationships = DB::table('rl_address_connections')
            ->where('rl_address_connections.from_person_id', $person->id)
            ->join('rl_people', 'rl_address_connections.to_person_id', '=', 'rl_people.id')
            ->paginate(10);

        return response()->json($relationships);
    }


    function getPersonGraphInfo($mainPersonId)
    {
        $sqlQuery = "SELECT * from 
                (SELECT a.id, a.name FROM rl_addresses a JOIN
                rl_address_people ap ON a.id = ap.address_id

                WHERE ap.person_id = " . $mainPersonId . "

                UNION

                SELECT a.id, a.name FROM rl_addresses a JOIN                   -- labs
                rl_address_people ap ON a.id = ap.address_id JOIN              -- from people
                rl_address_connections ac ON ap.person_id = ac.from_person_id  -- related to (from)
                WHERE ac.from_person_id = " . $mainPersonId . "                -- the main node 

                UNION

                SELECT a.id, a.name FROM rl_addresses a JOIN                   -- labs
                rl_address_people ap ON a.id = ap.address_id JOIN              -- from people
                rl_address_connections ac ON ap.person_id = ac.to_person_id    -- related to (to) 
                WHERE ac.from_person_id = " . $mainPersonId . "                -- the main node 

                ) related_labs";


        $related_labs = DB::select(DB::raw($sqlQuery));

        $related_labs_ids = Address::createRelatedLabsIds($related_labs);

        // query the lab workers
        $lab_workers = People::getLabWorkers($related_labs_ids);

        // get the people that work on related labs
        $related_people = People::getPersonInfo($mainPersonId);
        if ($related_labs_ids != ""){
            $related_people = array_merge($related_people, People::getRelatedLabsPeople($related_labs_ids));
        }

        // get the relations from related people
        $related_people_ids = People::createRelatedPeopleIds($related_people);

        // get relationships and descriptions
        $people_relationships = [];
        if ($related_people_ids != ""){
            $people_relationships = People::getPeopleRelationships($related_people_ids);
        }

        $result = [ 'related_labs' => $related_labs, 'related_people' => $related_people, 'relationships' => $people_relationships, 'workers' => $lab_workers ];

        return response()->json($result);

    }
}
