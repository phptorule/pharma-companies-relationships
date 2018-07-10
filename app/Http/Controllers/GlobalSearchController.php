<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\People;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GlobalSearchController extends Controller
{


    function searchForAutoSuggesting()
    {
        $searchStr = request()->get('search');

        $searchIterations = request()->get('iteration');

        if(empty($searchIterations)) {

            $countOrganisations = Address::where('name', 'like', '%'.$searchStr.'%')->count();

            $countAddresses = Address::where('address', 'like', '%'.$searchStr.'%')->count();

            $countPeople = People::where('name', 'like', '%'.$searchStr.'%')->count();

            $countProduct = Product::where('company', 'like', '%'.$searchStr.'%')->orWhere('name', 'like', '%'.$searchStr.'%')->count();
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

        $this->_subQueryForAddressEntity($query, $groupedSearchIterations);

        return $query->count();
    }


    function findAddressMatches($searchStr, $groupedSearchIterations)
    {
        $query = Address::where('address', 'like', '%'.$searchStr.'%');

        $this->_subQueryForAddressEntity($query, $groupedSearchIterations);

        return $query->count();
    }


    private function _subQueryForAddressEntity($query, $groupedSearchIterations)
    {

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
                $query->whereHas('people', function($q) use ($person) {
                    $q->where('name', 'like', '%'.$person.'%');
                });
            }
        }

        if(!empty($groupedSearchIterations['products'])) {
            foreach ($groupedSearchIterations['products'] as $product) {
                $query->whereHas('products', function($q) use ($product) {
                    $q->where('company', 'like', '%'.$product.'%');
                    $q->orWhere('name', 'like', '%'.$product.'%');
                });
            }
        }

        return $query;
    }


    function findProductMatches($searchStr, $groupedSearchIterations)
    {
        $query = Product::where('company', 'like', '%'.$searchStr.'%')->orWhere('name', 'like', '%'.$searchStr.'%');

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
                        $q->where('name', 'like', '%'.$person.'%');
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
        $query = People::where('name', 'like', '%'.$searchStr.'%');

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
                $query->where('rl_people.name', 'like', '%'.$person.'%');
            }
        }

        if(!empty($groupedSearchIterations['products'])) {
            foreach ($groupedSearchIterations['products'] as $product) {
                $query->whereHas('addresses', function($q_a) use ($product) {

                    $q_a->whereHas('products', function($q) use ($product) {
                        $q->where('company', 'like', '%'.$product.'%');
                        $q->orWhere('name', 'like', '%'.$product.'%');
                    });

                });
            }
        }

        return $query->count();
    }
}
