<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\ConnectionTypes;
use App\Models\People;
use App\Models\Publication;
use App\Models\PeopleType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use MongoDB\BSON\Persistable;

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
        $person->load('publications');
        // $person->relationships = DB::table('rl_address_connections AS rl1')
        //     ->select(DB::raw("from_person_id, to_person_id, SUM(edge_weight) as edge_weight, edge_type, from_address_id, to_address_id,
        //     (SELECT a1.edge_comment FROM rl_address_connections as a1 WHERE from_person_id = $person->id AND rl1.to_person_id = to_person_id AND a1.edge_type = '1' GROUP BY to_person_id) as co_authored_paper,
        //     (SELECT a2.edge_comment FROM rl_address_connections as a2 WHERE from_person_id = $person->id AND rl1.to_person_id = to_person_id AND a2.edge_type = '2' GROUP BY to_person_id) as cited_paper,
        //     (SELECT a3.edge_comment FROM rl_address_connections as a3 WHERE from_person_id = $person->id AND rl1.to_person_id = to_person_id AND a3.edge_type = '3' GROUP BY to_person_id) as signatory_at_company,
        //     COUNT(to_person_id) AS count_types, 
        //     rl_people.*"))
        // $sql = "
        //     SELECT 
        //     rl1.from_person_id, 
        //     rl1.to_person_id, 
        //     SUM(rl1.edge_weight) as edge_weight, 
        //     rl1.edge_type,
        //     ( case when rl1.edge_type = '1' then rl1.edge_comment end ) as 'co_authored_paper',
        //     ( case when rl1.edge_type = '2' then rl1.edge_comment end ) as 'cited_paper',
        //     ( case when rl1.edge_type = '3' then rl1.edge_comment end ) as 'signatory_at_company',
        //     ( case when rl1.edge_type = '4' then rl1.edge_comment end ) as 'manual',
        //     COUNT(rl1.to_person_id) AS count_types, 
        //     rl_people.*
        //     FROM `rl_address_connections` rl1
        //     JOIN rl_people on rl1.to_person_id = rl_people.id
        //     WHERE rl1.from_person_id = $person->id
        //     group by rl1.to_person_id
        // ";
        $person->relationships = DB::table('rl_address_connections as rl1')
            ->select(DB::raw("
                rl1.from_person_id, 
                rl1.to_person_id, 
                SUM(rl1.edge_weight) as edge_weight, 
                rl1.edge_type,
                ( case when rl1.edge_type = '1' then rl1.edge_comment end ) as 'co_authored_paper',
                ( case when rl1.edge_type = '2' then rl1.edge_comment end ) as 'cited_paper',
                ( case when rl1.edge_type = '3' then rl1.edge_comment end ) as 'signatory_at_company',
                ( case when rl1.edge_type = '4' then rl1.edge_comment end ) as 'manual',
                COUNT(rl1.to_person_id) AS count_types, 
                rl_people.*
            "))
            ->join('rl_people', 'rl1.to_person_id', '=', 'rl_people.id')
            ->where('rl1.from_person_id', $person->id)
            ->groupBy('rl1.to_person_id')
            ->orderBy('name', 'ASC')
            ->get();

            $person->relationships->each(function ($relation, $key) {
                $sqlQuery = "
                    SELECT MAX(year) as year FROM `rl_publications` 
                    JOIN rl_people_publications 
                    ON rl_people_publications.person_id = $relation->to_person_id
                    AND rl_publications.id = rl_people_publications.publication_id
                ";
                $lastCooperationYear = DB::select(DB::raw($sqlQuery));
                $relation->lastCooperationYear = $lastCooperationYear;
            });

            return response()->json($person);
    }


    function getConnectionTypes()
    {
        return response()->json(ConnectionTypes::all());
    }


    function getPersonRelationships(People $person)
    {
        // $relationships = DB::table('rl_address_connections AS rl1')
        //     ->select(DB::raw("from_person_id, to_person_id, SUM(edge_weight) as edge_weight, from_address_id, to_address_id,
        //     (SELECT a1.edge_comment FROM rl_address_connections as a1 WHERE from_person_id = $person->id AND rl1.to_person_id = to_person_id AND a1.edge_type = '1' GROUP BY to_person_id) as co_authored_paper,
        //     (SELECT a2.edge_comment FROM rl_address_connections as a2 WHERE from_person_id = $person->id AND rl1.to_person_id = to_person_id AND a2.edge_type = '2' GROUP BY to_person_id) as cited_paper,
        //     (SELECT a3.edge_comment FROM rl_address_connections as a3 WHERE from_person_id = $person->id AND rl1.to_person_id = to_person_id AND a3.edge_type = '3' GROUP BY to_person_id) as signatory_at_company,
        //     COUNT(to_person_id) AS count_types, 
        //     rl_people.*"))
        $relationships = DB::table('rl_address_connections as rl1')
            ->select(DB::raw("
            rl1.from_person_id, 
            rl1.to_person_id, 
            SUM(rl1.edge_weight) as edge_weight, 
            rl1.edge_type,
            ( case when rl1.edge_type = '1' then rl1.edge_comment end ) as 'co_authored_paper',
            ( case when rl1.edge_type = '2' then rl1.edge_comment end ) as 'cited_paper',
            ( case when rl1.edge_type = '3' then rl1.edge_comment end ) as 'signatory_at_company',
            ( case when rl1.edge_type = '4' then rl1.edge_comment end ) as 'manual',
            COUNT(rl1.to_person_id) AS count_types, 
            rl_people.*
            "))
            ->join('rl_people', 'rl1.to_person_id', '=', 'rl_people.id')
            ->where('rl1.from_person_id', $person->id)
            ->groupBy('rl1.to_person_id')
            ->orderBy('edge_weight', 'DESC')
            ->paginate(10);

            $relationships->each(function ($relation, $key) {
                $sqlQuery = "
                    SELECT MAX(year) as year FROM `rl_publications` 
                    JOIN rl_people_publications 
                    ON rl_people_publications.person_id = $relation->to_person_id
                    AND rl_publications.id = rl_people_publications.publication_id
                ";
                $lastCooperationYear = DB::select(DB::raw($sqlQuery));
                $relation->lastCooperationYear = $lastCooperationYear;
            });

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


    function getRelationshipDetails(People $person)
    {
        $request = request()->all();

        $responseData = [
            'co_authored_paper' => [],
            'cited_paper' => []
        ];

        if(isset($request['co_authored_paper'])) {
            $responseData['co_authored_paper'] = Publication::fetchArticlesByIds(explode(',', $request['co_authored_paper']));
        }

        if(isset($request['cited_paper'])) {
            $responseData['cited_paper'] = Publication::fetchArticlesByIds(explode(',',$request['cited_paper']));
        }

        return response()->json($responseData);
    }
    
    function updateEmploye(People $person)
    {
        $person->name = request('name');
        $person->description = request('description');
        $person->role = request('role');
        $person->linkedin_url = request('linkedin_url');
        $person->twitter = request('twitter');
        $person->facebook = request('facebook');
        $person->instagram = request('instagram');
        $person->telegram = request('telegram');
        if (trim($person->role) == '') {
            $person->role = null;
        }
        if (trim($person->linkedin_url) == '') {
            $person->linkedin_url = null;
        }
        if (trim($person->twitter) == '') {
            $person->twitter = null;
        }
        if (trim($person->facebook) == '') {
            $person->facebook = null;
        }
        if (trim($person->instagram) == '') {
            $person->instagram = null;
        }
        if (trim($person->telegram) == '') {
            $person->telegram = null;
        }
        $person->save();
        return response()->json($person);
    }

    function getRoles ()
    {
        $roles = PeopleType::get();
        return response()->json($roles);
    }


    function getPeoplePaginated() {

        $query = People::with('addresses');

        $query = $this->composeConditions($query, request()->all());

        $people = $query->paginate(20);

        return response()->json($people);
    }


    function composeConditions($query, $params)
    {
        if (isset($params['person-type-id'])) {
            $query->where('type_id', $params['person-type-id']);
        }

        if (isset($params['sort-by'])) {
            $direction = $params['sort-by'] == 'name-asc' ? 'ASC' : 'DESC';

            $query->orderBy('name', $direction);
        }

        if(isset($params['role'])) {
            $query->where('rl_people.role', 'like', '%'.$params['role'].'%');
        }

        if(isset($params['global-search'])) {
            $query->where('rl_people.name', 'like', '%'.$params['global-search'].'%');
        }

        if(isset($params['only-people-with-addresses'])) {
            $query->whereHas('addresses');
        }

        return $query;
    }


    function getPeopleAutocomplete ($searchQuery, $fromPersonId)
    {
        $people = People::with([
                'addresses'
            ])
            ->where('name', 'like', "%$searchQuery%")
            ->where('id', '<>', $fromPersonId)
            ->orderBy('name')
            ->paginate(5);

        $people->each(function ($person, $key) {
            $str = $person->town;
            $person->town = str_replace('.0', '', $str);
        });

        return response()->json($people);
    }


    function getDataForMap()
    {
        $conditions = $this->composeConditionsForMap(request()->all());

        $sql = "SELECT a.id, a.lat, a.lon, a.name, a.customer_status 
                FROM rl_addresses AS a
                INNER JOIN rl_address_people AS ap
                    ON ap.address_id = a.id
                INNER JOIN rl_people AS p
                    ON p.id = ap.person_id
                $conditions
                GROUP BY a.id";

        $addresses = DB::select(DB::raw($sql));

        return response()->json($addresses);
    }


    function composeConditionsForMap($params)
    {
        $conditionStr = 'WHERE a.id != -1';

        if (isset($params['person-type-id'])) {
            $conditionStr .= ' AND p.type_id = '.$params['person-type-id'] . ' ';
        }

        if(isset($params['role'])) {
            $conditionStr .= " AND p.role LIKE '%".$params['role']."%' ";
        }

        if(isset($params['global-search'])) {
            $conditionStr .= " AND p.name LIKE '%".$params['global-search']."%' ";
        }

        return $conditionStr;
    }

}
