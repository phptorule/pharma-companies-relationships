<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\People;
use App\Models\Product;
use App\Services\GlobalSearchService;
use Illuminate\Http\Request;
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

        $addressIds = $this->GSS->searchForAddressesIds($groupedSearchIterations)->pluck('id');

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
            $groupedSearchIterations = $this->groupSearchIterationsByEntity($searchIterations);

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
        ];

        return response()->json($responseData);
    }


    function groupSearchIterationsByEntity($searchIterations)
    {
        $groups = [
            'people' => [],
            'addresses' => [],
            'products' => [],
            'organisations' => [],
        ];

        if(!empty($searchIterations)) {

            $groups['organisations'] = array_filter($searchIterations, function ($element) {
                return strpos($element, 'Organisation/--/') !== false;
            });
            $groups['organisations'] = array_map(function ($el){
                return str_replace('Organisation/--/', '', $el);
            }, $groups['organisations']);


            $groups['people'] = array_filter($searchIterations, function ($element) {
                return strpos($element, 'Person/--/') !== false;
            });
            $groups['people'] = array_map(function ($el){
                return str_replace('Person/--/', '', $el);
            }, $groups['people']);


            $groups['addresses'] = array_filter($searchIterations, function ($element) {
                return strpos($element, 'Address/--/') !== false;
            });
            $groups['addresses'] = array_map(function ($el){
                return str_replace('Address/--/', '', $el);
            }, $groups['addresses']);


            $groups['products'] = array_filter($searchIterations, function ($element) {
                return strpos($element, 'Product/--/') !== false;
            });
            $groups['products'] = array_map(function ($el){
                return str_replace('Product/--/', '', $el);
            }, $groups['products']);
        }

        return $groups;
    }


    function findOrganisationMatches($searchStr, $groupedSearchIterations)
    {

        $query = Address::where('name', 'like', '%'.$searchStr.'%');

        $this->GSS->_subQueryForAddressEntity($query, $groupedSearchIterations);

        return $query->count();
    }


    function findAddressMatches($searchStr, $groupedSearchIterations)
    {
        $query = Address::where('address', 'like', '%'.$searchStr.'%');

        $this->GSS->_subQueryForAddressEntity($query, $groupedSearchIterations);

        return $query->count();
    }


    function findProductMatches($searchStr, $groupedSearchIterations)
    {
        $query = Product::where(function ($q) use ($searchStr) {
                        $q->orWhere('company', 'like', '%'.$searchStr.'%');
                        $q->orWhere('name', 'like', '%'.$searchStr.'%');
                    });

        if(!empty($groupedSearchIterations['organisations'])) {
            foreach ($groupedSearchIterations['organisations'] as $organisation) {
                $query->whereHas('addresses', function($q) use ($organisation) {
                    $q->where('rl_addresses.name', 'like', '%'.$organisation.'%');
                });
            }
        }

        if(!empty($groupedSearchIterations['addresses'])) {
            foreach ($groupedSearchIterations['addresses'] as $address) {
                $query->whereHas('addresses', function($q) use ($address) {
                    $q->where('rl_addresses.address', 'like', '%'.$address.'%');
                });
            }
        }

        if(!empty($groupedSearchIterations['people'])) {
            foreach ($groupedSearchIterations['people'] as $person) {
                $query->whereHas('addresses', function($q_a) use ($person) {

                    $q_a->whereHas('people', function($q) use ($person) {
                        $q->orWhere('rl_people.name', 'like', '%'.$person.'%');
                        $q->orWhere('rl_people.role', 'like', '%'.$person.'%');
                        $q->orWhere('rl_people.description', 'like', '%'.$person.'%');
                    });

                });
            }
        }

        if(!empty($groupedSearchIterations['products'])) {
            foreach ($groupedSearchIterations['products'] as $product) {
                $query->where('company', 'like', '%'.$product.'%')
                    ->orWhere('name', 'like', '%'.$product.'%');

            }
        }

        return $query->count();
    }


    function findPeopleMatches($searchStr, $groupedSearchIterations)
    {
        $query = People::where(function ($q) use ($searchStr) {
            $q->orWhere('name', 'like' , '%'.$searchStr.'%');
            $q->orWhere('role', 'like' , '%'.$searchStr.'%');
            $q->orWhere('description', 'like' , '%'.$searchStr.'%');
        });

        $this->GSS->_subQueryForPeopleEntity($query, $groupedSearchIterations);

        return $query->count();
    }
}
