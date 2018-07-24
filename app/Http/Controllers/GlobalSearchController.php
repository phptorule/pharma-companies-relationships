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
    public $isExtended;


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
        $paramsForFullTextSearch = $this->GSS->composeSearchStrForFulltextSearch($searchStr);

        $query = $this->GSS->setAddressJoins()->where(function($q) use($paramsForFullTextSearch){
                                                    $q->whereRaw("(
                                                                   MATCH (rl_addresses.name) AGAINST (? IN BOOLEAN MODE) 
                                                                   or MATCH (rl_clusters.name) AGAINST (? IN BOOLEAN MODE)
                                                                )", [$paramsForFullTextSearch, $paramsForFullTextSearch]
                                                    );
                                                });

        $this->GSS->_subQueryForIterations($query, $groupedSearchIterations);

        $fullTextResult = $query->count(DB::raw('DISTINCT(rl_addresses.id)'));
        
        $levenshteinSql = $this->GSS->composeConditionsForLevenshteinQuery($searchStr, ['rl_addresses.name', 'rl_clusters.name']);

        $query = $this->GSS->setAddressJoins()->whereRaw($levenshteinSql);

        $this->GSS->_subQueryForIterations($query, $groupedSearchIterations);

        $levenshteinResult = $query->count(DB::raw('DISTINCT(rl_addresses.id)'));

        return $fullTextResult + $levenshteinResult;
    }


    function findAddressMatches($searchStr, $groupedSearchIterations)
    {
        $paramsForFullTextSearch = $this->GSS->composeSearchStrForFulltextSearch($searchStr);

        $query = $this->GSS->setAddressJoins()->where(function($q) use($paramsForFullTextSearch){
                                                    $q->whereRaw("MATCH (rl_addresses.address) AGAINST (? IN BOOLEAN MODE)",
                                                        [$paramsForFullTextSearch]);
                                                });

        $this->GSS->_subQueryForIterations($query, $groupedSearchIterations);

        $fullTextResult = $query->count(DB::raw('DISTINCT(rl_addresses.id)'));


        $levenshteinSql = $this->GSS->composeConditionsForLevenshteinQuery($searchStr, ['rl_addresses.address']);

        $query = $this->GSS->setAddressJoins()->whereRaw($levenshteinSql);

        $this->GSS->_subQueryForIterations($query, $groupedSearchIterations);

        $levenshteinResult = $query->count(DB::raw('DISTINCT(rl_addresses.id)'));

        return $fullTextResult + $levenshteinResult;
    }


    function findProductMatches($searchStr, $groupedSearchIterations)
    {
        $paramsForFullTextSearch = $this->GSS->composeSearchStrForFulltextSearch($searchStr);

        $query = $this->GSS->setProductJoins()->where(function($q) use($paramsForFullTextSearch){
                                                    $q->whereRaw("(
                                                           MATCH (rl_products.company) AGAINST (? IN BOOLEAN MODE) 
                                                           or MATCH (rl_products.name) AGAINST (? IN BOOLEAN MODE)
                                                        )", [$paramsForFullTextSearch, $paramsForFullTextSearch]
                                                    );
                                                });


        $query = $this->GSS->_subQueryForIterations($query, $groupedSearchIterations);

        $fullTextResult = $query->count(DB::raw('DISTINCT(rl_products.id)'));


        $levenshteinSql = $this->GSS->composeConditionsForLevenshteinQuery($searchStr, ['rl_products.company', 'rl_products.name']);

        $query = $this->GSS->setProductJoins()->whereRaw($levenshteinSql);

        $query = $this->GSS->_subQueryForIterations($query, $groupedSearchIterations);

        $levenshteinResult = $query->count(DB::raw('DISTINCT(rl_products.id)'));

        return $fullTextResult + $levenshteinResult;
    }


    function findPeopleMatches($searchStr, $groupedSearchIterations)
    {
        $paramsForFullTextSearch = $this->GSS->composeSearchStrForFulltextSearch($searchStr);

        $query = $this->GSS->setPeopleJoins()
                            ->where(function($q) use($paramsForFullTextSearch){
                                $q->whereRaw("(
                                               MATCH (rl_people.name) AGAINST (? IN BOOLEAN MODE) 
                                               or MATCH (rl_people.role) AGAINST (? IN BOOLEAN MODE) 
                                               or MATCH (rl_people.description) AGAINST (? IN BOOLEAN MODE)
                                            )",
                                    [$paramsForFullTextSearch, $paramsForFullTextSearch, $paramsForFullTextSearch]);
                            });

        $query = $this->GSS->_subQueryForIterations($query, $groupedSearchIterations);

        $fullTextResult = $query->count(DB::raw('DISTINCT(rl_people.id)'));


        $levenshteinSql = $this->GSS->composeConditionsForLevenshteinQuery($searchStr, ['rl_people.name', 'rl_people.role', 'rl_people.description']);

        $query = $this->GSS->setPeopleJoins()->whereRaw($levenshteinSql);

        $query = $this->GSS->_subQueryForIterations($query, $groupedSearchIterations);

        $levenshteinResult = $query->count(DB::raw('DISTINCT(rl_people.id)'));

        return $fullTextResult + $levenshteinResult;
    }
}
