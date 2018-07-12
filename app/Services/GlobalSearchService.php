<?php
/**
 * Created by PhpStorm.
 * User: developer
 * Date: 11.07.18
 * Time: 15:05
 */

namespace App\Services;


use App\Models\Address;
use App\Models\People;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GlobalSearchService
{


    function setAddressJoins()
    {
        return DB::table('rl_addresses')

            ->leftJoin('rl_address_people', 'rl_addresses.id', '=', 'rl_address_people.address_id')
            ->leftJoin('rl_people', 'rl_address_people.person_id', '=', 'rl_people.id')

            ->leftJoin('rl_address_products', 'rl_addresses.id', '=', 'rl_address_products.address_id')
            ->leftJoin('rl_products', 'rl_address_products.address_id', '=', 'rl_products.id');
    }


    function searchForAddressesIds($groupedSearchIterations)
    {
        $query = $this->setAddressJoins()
                    ->selectRaw('DISTINCT(rl_addresses.id)');

        $query = $this->_subQueryForAddressEntity($query, $groupedSearchIterations);

        return $query->get();
    }


    function searchForPeopleIds($groupedSearchIterations)
    {
        $query = People::select('rl_people.id');

        $this->_subQueryForPeopleEntity($query, $groupedSearchIterations);

        return $query->get();
    }


    function groupSearchIterationsByEntity($searchIterations)
    {
        $groups = [
            'people' => [],
            'addresses' => [],
            'products' => [],
            'organisations' => [],
            'any' => [],
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


            $groups['any'] = array_filter($searchIterations, function ($element) {
                return strpos($element, 'Any/--/') !== false;
            });
            $groups['any'] = array_map(function ($el){
                return str_replace('Any/--/', '', $el);
            }, $groups['any']);
        }

        return $groups;
    }


    function _subQueryForAddressEntity($query, $groupedSearchIterations)
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

        return $query;
    }


    function _subQueryForPeopleEntity($query, $groupedSearchIterations)
    {
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
                $query->where(function ($q) use ($person){
                    $q->orWhere('rl_people.name', 'like', '%'.$person.'%');
                    $q->orWhere('rl_people.role', 'like', '%'.$person.'%');
                    $q->orWhere('rl_people.description', 'like', '%'.$person.'%');
                });
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

        return $query;
    }
}