<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\People;
use App\Models\Product;
use App\Services\GlobalSearchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GlobalSearchController extends Controller
{

    public $GSS;


    function __construct()
    {
        $this->GSS = new GlobalSearchService();
    }


    function index()
    {
        $si = request()->get('iteration');

        $groupedSearchIterations = $this->GSS->groupSearchIterationsByEntity($si);

        $addressIds = $this->GSS->searchForAddressesIds($groupedSearchIterations);

        $peopleIds =  $this->GSS->searchForPeopleIds($groupedSearchIterations)->pluck('id');

        return response()->json([
            'count_addresses' => count($addressIds),
            'address_ids' => $addressIds,
            'count_people' => count($peopleIds),
            'people_ids' => $peopleIds
        ]);
    }


    function searchForAutoSuggesting()
    {
        $searchStr = trim(request()->get('search'));

        $searchStr = $this->GSS->composeSearchStrForFulltextSearch($searchStr);

        $searchIterations = request()->get('iteration');

        $groupedSearchIterations = $this->GSS->groupSearchIterationsByEntity($searchIterations);

        $countOrganisations = $this->findOrganisationMatches($searchStr, $groupedSearchIterations);

        $countAddresses = $this->findAddressMatches($searchStr, $groupedSearchIterations);

        $countPeople = $this->findPeopleMatches($searchStr, $groupedSearchIterations);

        $countProduct = $this->findProductMatches($searchStr, $groupedSearchIterations);

        $countAny = $countOrganisations + $countAddresses + $countPeople + $countProduct;


        $responseData = [
            'Organisation' => $countOrganisations,
            'Address' => $countAddresses,
            'Person' => $countPeople,
            'Product' => $countProduct,
            'Any' => $countAny,
        ];

        return response()->json($responseData);
    }


    function findOrganisationMatches($searchStr, $groupedSearchIterations)
    {

        $query =  $this->GSS->setAddressJoins()
                            ->where(function($q) use ($searchStr){
                                $q->where('rl_addresses.name', 'like', '%'.$searchStr.'%');
                                $q->orWhere('rl_clusters.name', 'like', '%'.$searchStr.'%');
                            });

        $this->GSS->_subQueryForIterations($query, $groupedSearchIterations);

        return $query->count(DB::raw('DISTINCT(rl_addresses.id)'));
    }


    function findAddressMatches($searchStr, $groupedSearchIterations)
    {
        $query =  $this->GSS->setAddressJoins()
                            ->where('rl_addresses.address', 'like', '%'.$searchStr.'%');

        $this->GSS->_subQueryForIterations($query, $groupedSearchIterations);

        return $query->count(DB::raw('DISTINCT(rl_addresses.id)'));
    }


    function findProductMatches($searchStr, $groupedSearchIterations)
    {

        $query =  $this->GSS->setProductJoins()
                            ->whereRaw("(
                                   MATCH (rl_products.company) AGAINST (? IN BOOLEAN MODE) 
                                   or MATCH (rl_products.name) AGAINST (? IN BOOLEAN MODE)
                                )", [$searchStr, $searchStr]);

        $query = $this->GSS->_subQueryForIterations($query, $groupedSearchIterations);

        return $query->count(DB::raw('DISTINCT(rl_products.id)'));
    }


    function findPeopleMatches($searchStr, $groupedSearchIterations)
    {
        $query =  $this->GSS->setPeopleJoins()
                            ->whereRaw("(
                                   MATCH (rl_people.name) AGAINST (? IN BOOLEAN MODE) 
                                   or MATCH (rl_people.role) AGAINST (? IN BOOLEAN MODE) 
                                   or MATCH (rl_people.description) AGAINST (? IN BOOLEAN MODE)
                                )",
                            [$searchStr, $searchStr, $searchStr]);


        $query = $this->GSS->_subQueryForIterations($query, $groupedSearchIterations);

        return $query->count(DB::raw('DISTINCT(rl_people.id)'));
    }
}
