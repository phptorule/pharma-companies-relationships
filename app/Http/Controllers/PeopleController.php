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
        $relationships = DB::table('rl_address_connections AS rl1')
            ->select(DB::raw("from_person_id, to_person_id, MAX(edge_weight) as edge_weight,
            (SELECT a1.edge_comment FROM rl_address_connections as a1 WHERE from_person_id = $person->id AND rl1.to_person_id = to_person_id AND a1.edge_type = '1') as co_authored_paper,
            (SELECT a2.edge_comment FROM rl_address_connections as a2 WHERE from_person_id = $person->id AND rl1.to_person_id = to_person_id AND a2.edge_type = '2') as cited_paper,
            (SELECT a3.edge_comment FROM rl_address_connections as a3 WHERE from_person_id = $person->id AND rl1.to_person_id = to_person_id AND a3.edge_type = '3') as signatory_at_company,
            COUNT(to_person_id) AS count_types, 
            rl_people.*"))
            ->join('rl_people', 'rl1.to_person_id', '=', 'rl_people.id')
            ->where('from_person_id', $person->id)
            ->groupBy('to_person_id')
            ->orderBy('edge_weight', 'DESC')
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
