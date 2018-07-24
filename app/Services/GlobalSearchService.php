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

                $levenshteinSql = $this->composeConditionsForLevenshteinQuery($organisation, ['rl_addresses.name', 'rl_clusters.name']);

                $organisation = $this->composeSearchStrForFulltextSearch($organisation);

                $query->where(function ($q) use ($organisation) {
                        $q->whereRaw("(
                               MATCH (rl_addresses.name) AGAINST (? IN BOOLEAN MODE) 
                               or MATCH (rl_clusters.name) AGAINST (? IN BOOLEAN MODE)
                            )", [$organisation, $organisation]);
                        })
                    ->orWhere(function ($q) use ($levenshteinSql) {
                        $q->whereRaw($levenshteinSql);
                    });
            }
        }

        if(!empty($groupedSearchIterations['addresses'])) {
            foreach ($groupedSearchIterations['addresses'] as $address) {

                $levenshteinSql = $this->composeConditionsForLevenshteinQuery($address, ['rl_addresses.address']);

                $address = $this->composeSearchStrForFulltextSearch($address);

                $query->where(function ($q) use ($address) {
                        $q->whereRaw("MATCH (rl_addresses.address) AGAINST (? IN BOOLEAN MODE)", [$address]);
                    })
                    ->orWhere(function ($q) use ($levenshteinSql) {
                        $q->whereRaw($levenshteinSql);
                    });
            }
        }

        if(!empty($groupedSearchIterations['people'])) {
            foreach ($groupedSearchIterations['people'] as $person) {

                $levenshteinSql = $this->composeConditionsForLevenshteinQuery($person, ['rl_people.name', 'rl_people.role', 'rl_people.description']);

                $person = $this->composeSearchStrForFulltextSearch($person);

                $query->where(function($q) use ($person) {
                        $q->whereRaw("(
                           MATCH (rl_people.name) AGAINST (? IN BOOLEAN MODE) 
                           or MATCH (rl_people.role) AGAINST (? IN BOOLEAN MODE) 
                           or MATCH (rl_people.description) AGAINST (? IN BOOLEAN MODE)
                        )", [$person,$person,$person]);
                    })
                    ->orWhere(function ($q) use ($levenshteinSql) {
                        $q->whereRaw($levenshteinSql);
                    });
            }
        }

        if(!empty($groupedSearchIterations['products'])) {
            foreach ($groupedSearchIterations['products'] as $product) {

                $levenshteinSql = $this->composeConditionsForLevenshteinQuery($product, ['rl_products.company', 'rl_products.name']);

                $product = $this->composeSearchStrForFulltextSearch($product);

                $query->where(function($q) use ($product) {
                        $q->whereRaw("(
                           MATCH (rl_products.company) AGAINST (? IN BOOLEAN MODE) 
                           or MATCH (rl_products.name) AGAINST (? IN BOOLEAN MODE) 
                        )", [$product,$product]);
                    })
                    ->orWhere(function ($q) use ($levenshteinSql) {
                        $q->whereRaw($levenshteinSql);
                    });
            }
        }

        if(!empty($groupedSearchIterations['any'])) {
            foreach ($groupedSearchIterations['any'] as $any) {

                $levCompanySql = $this->composeConditionsForLevenshteinQuery($any, ['rl_addresses.name', 'rl_clusters.name']);

                $levAddressSql = $this->composeConditionsForLevenshteinQuery($any, ['rl_addresses.address']);

                $levPeopSql = $this->composeConditionsForLevenshteinQuery($any, ['rl_people.name', 'rl_people.role', 'rl_people.description']);

                $levProdSql = $this->composeConditionsForLevenshteinQuery($any, ['rl_products.company', 'rl_products.name']);

                $any = $this->composeSearchStrForFulltextSearch($any);

                $query->where(function($q) use ($any) {
                        $q->whereRaw("(
                               (MATCH (rl_addresses.name) AGAINST (? IN BOOLEAN MODE) 
                               or MATCH (rl_clusters.name) AGAINST (? IN BOOLEAN MODE))
                           OR                                
                               (MATCH (rl_addresses.address) AGAINST (? IN BOOLEAN MODE))
                           OR
                               (MATCH (rl_people.name) AGAINST (? IN BOOLEAN MODE) 
                               or MATCH (rl_people.role) AGAINST (? IN BOOLEAN MODE) 
                               or MATCH (rl_people.description) AGAINST (? IN BOOLEAN MODE))
                           OR
                               (MATCH (rl_products.company) AGAINST (? IN BOOLEAN MODE) 
                               or MATCH (rl_products.name) AGAINST (? IN BOOLEAN MODE))
                        )",
                        [$any, $any, $any, $any, $any, $any, $any, $any]);
                    })
                    ->orWhere(function ($q) use ($levCompanySql) {
                        $q->whereRaw($levCompanySql);
                    })
                    ->orWhere(function ($q) use ($levAddressSql) {
                        $q->whereRaw($levAddressSql);
                    })
                    ->orWhere(function ($q) use ($levPeopSql) {
                        $q->whereRaw($levPeopSql);
                    })
                    ->orWhere(function ($q) use ($levProdSql) {
                        $q->whereRaw($levProdSql);
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
            if(!empty($word)){
                $string .= '+'.$word.'* ';
            }
        }

        return trim($string);
    }


    function composeConditionsForLevenshteinQuery($strQuery, array $fields)
    {
        $searchWordsArr = [];

        $bindings = [];

        $sql = '';

        foreach (explode(' ', $strQuery) as $word) {
            $words = [];
            for ($i = 0; $i < strlen($word); $i++) {
                // insertions
                $words[] = substr($word, 0, $i) . '_' . substr($word, $i);
                // deletions
                $words[] = substr($word, 0, $i) . substr($word, $i + 1);
                // substitutions
                $words[] = substr($word, 0, $i) . '_' . substr($word, $i + 1);
            }
            // last insertion
            $words[] = $word . '_';

            $searchWordsArr[] = $words;

            $bindings = array_merge($bindings, $words);
        }

        foreach ($fields as $i => $field) {

            $sql .= '(';

            if(count($fields) > 1) {
                $sql .= '(';
            }

            foreach ($searchWordsArr as $j => $wordVarieties) {

                foreach ($wordVarieties as $k => $variety) {

                    $sql .= "$field LIKE '%$variety%' ";

                    if($k+1 != count($wordVarieties)) {
                        $sql .= "OR ";
                    }

                }

                if($j+1 != count($searchWordsArr)) {
                    $sql .= ') AND (';
                }
                else {
                    $sql .= ')';
                }

            }

            if($i+1 != count($fields) && count($fields) > 1) {
                $sql .= ') OR ';
            }
            else if(count($fields) > 1) {
                $sql .= ')';
            }

        }

        return $sql;
    }
}