<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\ConnectionTypes;
use App\Models\People;
use App\Models\Publication;
use App\Models\PeopleType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
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

        $person->relationships = DB::table('rl_address_connections AS rl1')
            ->select(DB::raw("from_person_id, to_person_id, SUM(edge_weight) as edge_weight, edge_type, from_address_id, to_address_id,
            (SELECT a1.edge_comment FROM rl_address_connections as a1 WHERE from_person_id = $person->id AND rl1.to_person_id = to_person_id AND a1.edge_type = '1' GROUP BY to_person_id) as co_authored_paper,
            (SELECT a2.edge_comment FROM rl_address_connections as a2 WHERE from_person_id = $person->id AND rl1.to_person_id = to_person_id AND a2.edge_type = '2' GROUP BY to_person_id) as cited_paper,
            (SELECT a3.edge_comment FROM rl_address_connections as a3 WHERE from_person_id = $person->id AND rl1.to_person_id = to_person_id AND a3.edge_type = '3' GROUP BY to_person_id) as signatory_at_company,
            COUNT(to_person_id) AS count_types, 
            rl_people.*"))
            ->join('rl_people', 'rl1.to_person_id', '=', 'rl_people.id')
            ->where('from_person_id', $person->id)
            ->groupBy('to_person_id')
            ->orderBy('id', 'ASC')
            ->get();

        $this->defineLastCooperationYear($person->relationships);
        $this->defineRelatedPersonAddresses($person->relationships);

        return response()->json($person);
    }


    function getConnectionTypes()
    {
        return response()->json(ConnectionTypes::all());
    }


    function defineLastCooperationYear($relationships)
    {
        $relationships->each(function ($relation, $key) {
            $sqlQuery = "
                    SELECT MAX(year) as year FROM `rl_publications` 
                    JOIN rl_people_publications 
                    ON rl_people_publications.person_id = $relation->to_person_id
                    AND rl_people_publications.person_id  = $relation->from_person_id
                    AND rl_publications.id = rl_people_publications.publication_id
                ";
            $lastCooperationYear = DB::select(DB::raw($sqlQuery));
            $relation->lastCooperationYear = count($lastCooperationYear[0]) ? $lastCooperationYear[0]->year : null;
        });
    }


    function defineRelatedPersonAddresses($relationships)
    {
        $relationships->each(function ($relation, $k) {
            $relation->addresses = People::getAllPersonRelatedAddresses($relation->to_person_id);
        });
    }


    function getPersonRelationships(People $person)
    {
        $params = request()->all();

        $coAuthIdsSql = "SELECT a1.edge_comment FROM rl_address_connections as a1 WHERE from_person_id = $person->id AND rl1.to_person_id = to_person_id AND a1.edge_type = '1'";
        $citedIdsSql = "SELECT a2.edge_comment FROM rl_address_connections as a2 WHERE from_person_id = $person->id AND rl1.to_person_id = to_person_id AND a2.edge_type = '2'";

        $sqlQuery = DB::table('rl_address_connections AS rl1')
            ->select(DB::raw("from_person_id, to_person_id, SUM(edge_weight) as edge_weight, from_address_id, to_address_id,
            ($coAuthIdsSql GROUP BY to_person_id) as co_authored_paper,
            ($citedIdsSql GROUP BY to_person_id) as cited_paper,
            (SELECT a3.edge_comment FROM rl_address_connections as a3 WHERE from_person_id = $person->id AND rl1.to_person_id = to_person_id AND a3.edge_type = '3' GROUP BY to_person_id) as signatory_at_company,
            (SELECT MAX(p.year) FROM rl_publications AS p WHERE p.id IN (GROUP_CONCAT(DISTINCT(($coAuthIdsSql)) SEPARATOR ','), GROUP_CONCAT(DISTINCT(($citedIdsSql)) SEPARATOR ','))) as lastCooperationYear,
            COUNT(to_person_id) AS count_types, 
            rl_people.*"))
            ->join('rl_people', 'rl1.to_person_id', '=', 'rl_people.id')
            ->where('from_person_id', $person->id);

        $sqlQuery = $this->composeRelationshipQuery($sqlQuery, $params)
            ->groupBy('to_person_id');

        $relationships = $this->composeOrderBy($sqlQuery, $params)
//            ->paginate(10);
            ->get();

        $relationships = $this->paginate($relationships, 10, isset($params['page'])? $params['page'] : 1);

        $this->defineRelatedPersonAddresses($relationships);

        $responseData = json_decode(json_encode($relationships));

        $data = [];

        foreach ($responseData->data as $obj) {
            $data[] = $obj;
        }

        $responseData->data = $data;

        return response()->json($responseData);
    }


    public function paginate($items, $perPage = 15, $page = null, $options = [])
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }


    public function composeRelationshipQuery($q, $params)
    {
        if(isset($params['search'])) {
            $q->where('rl_people.name', 'like', '%'.$params['search'].'%');
        };

        if(isset($params['type-id'])) {
            $q->whereIn('rl1.edge_type', explode(',', $params['type-id']));
        };

        return $q;
    }


    function composeOrderBy($q, $params)
    {
        if (isset($params['sort-by'])) {

            $field = explode('-',$params['sort-by'])[0];
            $direction = explode('-',$params['sort-by'])[1];

            if($field == 'name') {
                $field = 'rl_people.name';
            }
            else if($field == 'date') {
                $field .= 'lastCooperationYear';
            }

            $q->orderBy($field,$direction);
        }
        else {
            $q->orderBy('edge_weight', 'DESC');
        }

        return $q;
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
