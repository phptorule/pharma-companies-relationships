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

            ->leftJoin('rl_clusters', 'rl_addresses.cluster_id', '=', 'rl_clusters.id')

            ->leftJoin('rl_address_people', 'rl_addresses.id', '=', 'rl_address_people.address_id')
            ->leftJoin('rl_people', 'rl_address_people.person_id', '=', 'rl_people.id')

            ->leftJoin('rl_address_products', 'rl_addresses.id', '=', 'rl_address_products.address_id')
            ->leftJoin('rl_products', 'rl_address_products.product_id', '=', 'rl_products.id');
    }


    function setPeopleJoins()
    {
        return DB::table('rl_people')

            ->leftJoin('rl_address_people', 'rl_people.id', '=', 'rl_address_people.person_id')
            ->leftJoin('rl_addresses', 'rl_address_people.address_id', '=', 'rl_addresses.id')

            ->leftJoin('rl_clusters', 'rl_addresses.cluster_id', '=', 'rl_clusters.id')

            ->leftJoin('rl_address_products', 'rl_addresses.id', '=', 'rl_address_products.address_id')
            ->leftJoin('rl_products', 'rl_address_products.product_id', '=', 'rl_products.id');
    }


    function setProductJoins()
    {
        return DB::table('rl_products')

            ->leftJoin('rl_address_products', 'rl_products.id', '=', 'rl_address_products.product_id')
            ->leftJoin('rl_addresses', 'rl_address_products.address_id', '=', 'rl_addresses.id')

            ->leftJoin('rl_clusters', 'rl_addresses.cluster_id', '=', 'rl_clusters.id')

            ->leftJoin('rl_address_people', 'rl_addresses.id', '=', 'rl_address_people.address_id')
            ->leftJoin('rl_people', 'rl_address_people.person_id', '=', 'rl_people.id');

    }


    function searchForAddressesIds($groupedSearchIterations)
    {
        $query = $this->setAddressJoins()
                    ->selectRaw('DISTINCT(rl_addresses.id)');

        $query = $this->_subQueryForIterations($query, $groupedSearchIterations);

        return $query->get();
    }


    function searchForPeopleIds($groupedSearchIterations)
    {
        $query = $this->setPeopleJoins()
                    ->selectRaw('DISTINCT(rl_people.id)');

        $query = $this->_subQueryForIterations($query, $groupedSearchIterations);

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


    function _subQueryForIterations($query, $groupedSearchIterations)
    {
        if(!empty($groupedSearchIterations['organisations'])) {
            foreach ($groupedSearchIterations['organisations'] as $organisation) {

                $organisation = $this->composeSearchStrForFulltextSearch($organisation);

                $query->where(function ($q) use ($organisation) {
                    $q->whereRaw("(
                                   MATCH (rl_addresses.name) AGAINST (? IN BOOLEAN MODE) 
                                   or MATCH (rl_clusters.name) AGAINST (? IN BOOLEAN MODE)
                                )", [$organisation, $organisation]);
                });
            }
        }

        if(!empty($groupedSearchIterations['addresses'])) {
            foreach ($groupedSearchIterations['addresses'] as $address) {

                $address = $this->composeSearchStrForFulltextSearch($address);

                $query->where(function ($q) use ($address) {
                    $q->whereRaw("MATCH (rl_addresses.address) AGAINST (? IN BOOLEAN MODE)", [$address]);
                });
            }
        }

        if(!empty($groupedSearchIterations['people'])) {
            foreach ($groupedSearchIterations['people'] as $person) {

                $person = $this->composeSearchStrForFulltextSearch($person);

                $query->where(function($q) use ($person) {
                    $q->whereRaw("(
                                   MATCH (rl_people.name) AGAINST (? IN BOOLEAN MODE) 
                                   or MATCH (rl_people.role) AGAINST (? IN BOOLEAN MODE) 
                                   or MATCH (rl_people.description) AGAINST (? IN BOOLEAN MODE)
                                )", [$person,$person,$person]);
                });
            }
        }

        if(!empty($groupedSearchIterations['products'])) {
            foreach ($groupedSearchIterations['products'] as $product) {

                $product = $this->composeSearchStrForFulltextSearch($product);

                $query->where(function($q) use ($product) {
                    $q->whereRaw("(
                                   MATCH (rl_products.company) AGAINST (? IN BOOLEAN MODE) 
                                   or MATCH (rl_products.name) AGAINST (? IN BOOLEAN MODE) 
                                )", [$product,$product]);
                });
            }
        }

        if(!empty($groupedSearchIterations['any'])) {
            foreach ($groupedSearchIterations['any'] as $any) {
                $query->where(function($q) use ($any) {

                    $q->where('rl_addresses.name', 'like', '%'.$any.'%');
                    $q->orWhere('rl_addresses.address', 'like', '%'.$any.'%');
                    $q->orWhere('rl_clusters.name', 'like', '%'.$any.'%');

                    $q->orWhere('rl_people.name', 'like', '%'.$any.'%');
                    $q->orWhere('rl_people.role', 'like', '%'.$any.'%');
                    $q->orWhere('rl_people.description', 'like', '%'.$any.'%');

                    $q->orWhere('rl_products.company', 'like', '%'.$any.'%');
                    $q->orWhere('rl_products.name', 'like', '%'.$any.'%');
                });
            }
        }

        return $query;
    }

    function composeSearchStrForFulltextSearch($strQuery)
    {
        $string = '';

        $arr = explode(' ', $strQuery);

        foreach ($arr as $word) {
            $string .= '+'.$word.'* ';
        }

        return trim($string);
    }
}