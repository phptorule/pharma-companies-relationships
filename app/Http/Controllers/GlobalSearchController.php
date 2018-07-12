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
        $searchStr = request()->get('search');

        $searchIterations = request()->get('iteration');

        if(empty($searchIterations)) {

            $countOrganisations = Address::where('name', 'like', '%'.$searchStr.'%')->count();

            $countAddresses = Address::where('address', 'like', '%'.$searchStr.'%')->count();

            $countPeople = People::where(function ($q) use ($searchStr) {
                                    $q->orWhere('name', 'like' , '%'.$searchStr.'%');
                                    $q->orWhere('role', 'like' , '%'.$searchStr.'%');
                                    $q->orWhere('description', 'like' , '%'.$searchStr.'%');
                                })
                                ->count();

            $countProduct = Product::where(function ($q) use ($searchStr) {
                                    $q->orWhere('company', 'like', '%'.$searchStr.'%');
                                    $q->orWhere('name', 'like', '%'.$searchStr.'%');
                                })
                                ->count();
        }
        else {
            $groupedSearchIterations = $this->GSS->groupSearchIterationsByEntity($searchIterations);

            $countOrganisations = $this->findOrganisationMatches($searchStr, $groupedSearchIterations);

            $countAddresses = $this->findAddressMatches($searchStr, $groupedSearchIterations);

            $countPeople = $this->findPeopleMatches($searchStr, $groupedSearchIterations);

            $countProduct = $this->findProductMatches($searchStr, $groupedSearchIterations);

        }


        $responseData = [
            'Organisation' => $countOrganisations,
            'Address' => $countAddresses,
            'Person' => $countPeople,
            'Product' => $countProduct,
            'Any' => 100,
        ];

        return response()->json($responseData);
    }


    function findOrganisationMatches($searchStr, $groupedSearchIterations)
    {

        $query =  $this->GSS->setAddressJoins()
                            ->where('rl_addresses.name', 'like', '%'.$searchStr.'%');

        $this->GSS->_subQueryForAddressEntity($query, $groupedSearchIterations);

        return $query->count(DB::raw('DISTINCT(rl_addresses.id)'));
    }


    function findAddressMatches($searchStr, $groupedSearchIterations)
    {
        $query =  $this->GSS->setAddressJoins()
                            ->where('rl_addresses.address', 'like', '%'.$searchStr.'%');

        $this->GSS->_subQueryForAddressEntity($query, $groupedSearchIterations);

        return $query->count(DB::raw('DISTINCT(rl_addresses.id)'));
    }


    function findProductMatches($searchStr, $groupedSearchIterations)
    {

        $query =  $this->GSS->setProductJoins()
                            ->where(function($q) use ($searchStr) {
                                $q->where('rl_products.company', 'like', '%'.$searchStr.'%');
                                $q->orWhere('rl_products.name', 'like', '%'.$searchStr.'%');
                            });

        if(!empty($groupedSearchIterations['organisations'])) {
            foreach ($groupedSearchIterations['organisations'] as $organisation) {
                $query->where('rl_addresses.name', 'like', '%'.$organisation.'%');
            }
        }

        if(!empty($groupedSearchIterations['addresses'])) {
            foreach ($groupedSearchIterations['addresses'] as $address) {
                $query->where('rl_addresses.address', 'like', '%'.$address.'%');
            }
        }

        if(!empty($groupedSearchIterations['people'])) {
            foreach ($groupedSearchIterations['people'] as $person) {
                $query->where(function($q) use ($person) {
                    $q->where('rl_people.name', 'like', '%'.$person.'%');
                    $q->orWhere('rl_people.role', 'like', '%'.$person.'%');
                    $q->orWhere('rl_people.description', 'like', '%'.$person.'%');
                });
            }
        }

        if(!empty($groupedSearchIterations['products'])) {
            foreach ($groupedSearchIterations['products'] as $product) {
                $query->where(function($q) use ($product) {
                    $q->where('rl_products.company', 'like', '%'.$product.'%');
                    $q->orWhere('rl_products.name', 'like', '%'.$product.'%');
                });
            }
        }

        return $query->count(DB::raw('DISTINCT(rl_products.id)'));
    }


    function findPeopleMatches($searchStr, $groupedSearchIterations)
    {
        $query =  $this->GSS->setPeopleJoins()
                            ->where(function($q) use ($searchStr) {
                                $q->where('rl_people.name', 'like' , '%'.$searchStr.'%')
                                    ->orWhere('rl_people.role', 'like' , '%'.$searchStr.'%')
                                    ->orWhere('rl_people.description', 'like' , '%'.$searchStr.'%');
                            });


        $query = $this->GSS->_subQueryForPeopleEntity($query, $groupedSearchIterations);

        return $query->count(DB::raw('DISTINCT(rl_people.id)'));
    }
}
