<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Product;
use App\Models\ProductConsumable;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProductsController extends Controller {

	public function productById( Product $product ) {

		return response()->json( $product );

	}

	public function productByTenders( $id, $address ) {

        $yearsUsed = DB::select(DB::raw(
            "select year(now()) - min(delivery_year) as yearsUsed from
                    (select sum(budget) as budgeted, delivery_year
                    from rl_address_tenders_budgets bu 
                    where bu.tender_id in 
                        (select tender_id 
                        from rl_address_tenders_purchase_products pro
                        inner join rl_address_tenders te 
                        on pro.tender_id = te.id
                        where te.address_id = $address 
                        and product_id=$id)
                    group by delivery_year) subq"
        ));

        $topSpent = DB::select(DB::raw(
            "select sum(budgeted) as topSpent from(
                select sum(budget) as budgeted, delivery_year
                from rl_address_tenders_budgets bu 
                where bu.tender_id in (
                    select tender_id 
                    from rl_address_tenders_purchase_products pro
                    inner join rl_address_tenders te 
                    on pro.tender_id = te.id
                    where te.address_id = $address 
                    and product_id= $id
                    and delivery_year < year(now())
                )
                group by delivery_year
            ) subq"
        ));

        $nextYearSpent = DB::select(DB::raw(
            "select sum(budgeted) as nextYearSpent from(
                select sum(budget) as budgeted, delivery_year
                from rl_address_tenders_budgets bu 
                where bu.tender_id in (
                    select tender_id 
                    from rl_address_tenders_purchase_products pro
                    inner join rl_address_tenders te 
                    on pro.tender_id = te.id
                    where te.address_id = $address 
                    and product_id=$id
                    and delivery_year = year(now())
                )
                group by delivery_year
            ) subq"
        ));


        $tags = DB::select(DB::raw("select GROUP_CONCAT(DISTINCT atpp.consumable_id SEPARATOR ', ') as tag_ids
            from rl_address_tenders_purchase_products as atpp
            left join rl_address_tenders as at on at.id = atpp.tender_id
            where atpp.product_id = $id
            and at.address_id = $address
            group by atpp.product_id
		"));



		return response()->json( [
			'years_used'    => $yearsUsed[0] ? $yearsUsed[0]->yearsUsed : 0,
			'max_total_spent'     => $topSpent[0] ? $topSpent[0]->topSpent : 0,
            'next_budgeted_cost' => $nextYearSpent[0] ? $nextYearSpent[0]->nextYearSpent : 0,
			'min_total_spent'     => null,
			'total_budgeted'      => null,
			'last_budgeted_cost'  => null,
			'first_budgeted_cost' => null,
            'tag_ids' => $tags[0] ? $tags[0]->tag_ids : ''
		] );

	}

	public function addressByProducts( $id ) {
		$query   = $this->getTendersByAddress( $id );
		$tenders = $query->orderBy( 'tender_date', 'asc' )->get();

		return response()->json( $tenders );
	}

    /**
     * Get paginated products for "top products" (left sidebar) and
     * "all products" (right pop-up sidebar) lists with pagination
     * and caching
     *
     * @param $address
     * @return \Illuminate\Http\JsonResponse
     */
	public function productByAddressPaginated( $address ) {

		$sql = "SELECT
                        prodId as id,
                        prodId as prod_id,
                        prodCompany as company,
                        prodName as name,
                        prodDescription as description,
                        prodImage as image,
                        pc_bud_sum as bud_sum,
                        pc_consum_name as consum_name,
                        SUM(budgetedCost) as total_spent, 
                        SUM(quantity) as volume,
                        MAX(units) as unit,
                        DATE_FORMAT(MAX(tdate), '%d-%m-%Y') as last_tender_date
                        FROM
						(
						    SELECT 
                            p.id as prodId,
                            p.company as prodCompany,
                            p.name as prodName,
                            p.description as prodDescription,
                            p.image as prodImage,
                            pc.bud_sum as pc_bud_sum,
                            pc.consum_name as pc_consum_name,
                            at.tender_date as tdate,
                            atp.units as units,
                            atp.quantity as quantity,
						    at.budgeted_cost as budgetedCost
							FROM rl_products as p
							LEFT JOIN rl_address_tenders_purchase_products AS atpp ON atpp.product_id = p.id
							LEFT JOIN rl_address_tenders_purchase AS atp ON atp.id = atpp.purchase_id
							LEFT JOIN rl_address_products AS ap ON ap.product_id = p.id
							RIGHT JOIN rl_address_tenders as at ON (at.address_id = ap.address_id and at.id = atp.tender_id)
							LEFT JOIN rl_address_tenders_budgets as atb ON atb.tender_id = at.id
                            LEFT JOIN (
                            SELECT SUM(at1.budgeted_cost) as bud_sum , pc1.name as consum_name, pc1.id as consum_id, p1.id as pr_id
                                FROM rl_address_tenders_budgets as atb1
                                LEFT JOIN rl_address_tenders AS at1 ON at1.id = atb1.tender_id
                                LEFT JOIN rl_address_tenders_purchase AS atp1 ON atp1.id = at1.id
                                LEFT JOIN rl_address_tenders_purchase_products AS atpp1 ON atpp1.purchase_id = atp1.id
                                LEFT JOIN rl_product_consumables AS pc1 ON pc1.id = atpp1.consumable_id
                                LEFT JOIN rl_products AS p1 ON p1.id = atpp1.product_id
                                WHERE at1.address_id = :address
                                AND pc1.id IS NOT NULL
                                GROUP BY pc1.id
                                ORDER BY bud_sum DESC
                           ) AS pc ON (pc.pr_id = p.id)
							WHERE at.address_id = :address1
							AND at.id IS NOT NULL
							GROUP BY at.id
						) as product
						WHERE prodId IS NOT NULL
                        GROUP BY prodId
                        ORDER BY total_spent DESC";

		// cache qeuery
        /*$result = \Illuminate\Support\Facades\Cache::remember(
            "loadTopProducts-" . md5($sql) . "-$address",
            60 * 24,
            function() use ($sql, $address) {
                return  DB::select(
                    DB::raw( $sql ),
                    [
                        'address' => $address,
                        'address1' => $address
                    ]
                );
            }
        );*/

        $result = DB::select(
            DB::raw( $sql ),
            [
                'address' => $address,
                'address1' => $address
            ]
        );

        // make new collection
        $collection = new \Illuminate\Support\Collection($result);

        // Paginate
        $perPage = 10; // Item per page
        $currentPage = request()->input('page', 1); // url.com/test?page=2
        $pagedData = $collection->slice(($currentPage-1) * $perPage, $perPage)->all();
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $pagedData,
            count($collection),
            $perPage,
            $currentPage
        );

		return response()->json( $paginator );
	}

	public function loadTagsValues() {

		$tags = ProductConsumable::get( [ 'id', 'name' ] );

		foreach ( $tags as $tag ) {
			$tag->color  = sprintf( '#%02X%02X%02X', rand( 0, 255 ), rand( 0, 255 ), rand( 0, 255 ) );
			$tagsColor[] = $tag;
		}

		array_push($tagsColor, [
		    'id' => 'empty',
            'name' => 'Others',
            'color' => '#666666'
        ]);

		return response()->json( $tagsColor );
	}

	public function TenderByProductChart( $id, $address ) {

		$select = "SELECT DATE_FORMAT(months, '%d/%m/%Y') as month, SUM(total) as total ";

		$sql = ' FROM ( SELECT at.tender_date as months, at.budgeted_cost as total ';

		// init array of parameters
        $paramsArr = [];

		$queryTags = '';

		if ( isset( request()->tags ) && $tags = request()->tags) {
		    if(!empty($tags) && is_array($tags)){

                foreach ( $tags as $tag ) {

                    if($tag == 'empty') {



                        $queryTags .= ",SUM(sum_tag$tag) as tag$tag ";

                        $sql .= ", (
                            SELECT at$tag.budgeted_cost
                            FROM rl_products AS p$tag
                            LEFT JOIN rl_address_tenders_purchase_products AS atpp$tag ON atpp$tag.product_id = p$tag.id
                            LEFT JOIN rl_address_tenders_purchase AS atp$tag ON atp$tag.id = atpp$tag.purchase_id
                            
                            LEFT JOIN rl_address_products AS ap$tag ON ap$tag.product_id = p$tag.id 
                            RIGHT JOIN rl_address_tenders AS at$tag ON (at$tag.address_id = ap$tag.address_id and at$tag.id = atp$tag.tender_id)
                            WHERE p$tag.id = $id
                            AND atpp$tag.consumable_id IS NULL
                            AND at$tag.address_id = $address
                            AND months = at$tag.tender_date
                            GROUP BY months
                            ) as sum_tag$tag ";

                        // put data for placeholders to array of parameters
                        array_push($paramsArr, $id, $address);

                    }
                    else {
                        // clear tag as get intval of it
                        $tag = intval($tag);

                        $queryTags .= ",SUM(sum_tag$tag) as tag$tag ";

                        $sql .= ", (
                            SELECT at$tag.budgeted_cost
                            FROM rl_products AS p$tag
                            LEFT JOIN rl_address_tenders_purchase_products AS atpp$tag ON atpp$tag.product_id = p$tag.id
                            LEFT JOIN rl_address_tenders_purchase AS atp$tag ON atp$tag.id = atpp$tag.purchase_id
                            LEFT JOIN rl_product_consumables AS pc$tag ON pc$tag.id = atpp$tag.consumable_id
                            LEFT JOIN rl_address_products AS ap$tag ON ap$tag.product_id = p$tag.id 
                            RIGHT JOIN rl_address_tenders AS at$tag ON (at$tag.address_id = ap$tag.address_id and at$tag.id = atp$tag.tender_id)
                            WHERE p$tag.id = $id
                            AND pc$tag.id = $tag
                            AND at$tag.address_id = $address
                            AND months = at$tag.tender_date
                            GROUP BY months
                            ) as sum_tag$tag ";

                        // put data for placeholders to array of parameters
                        array_push($paramsArr, $id, $address);
                    }
                }
            }
		}

		$sql .= " FROM rl_products as p 
					LEFT JOIN rl_address_tenders_purchase_products as atpp on atpp.product_id = p.id
					LEFT JOIN rl_address_tenders_purchase as atp on atp.id = atpp.purchase_id 
					LEFT JOIN rl_address_products as ap on ap.product_id = p.id
					LEFT JOIN rl_product_consumables as pc on pc.id = atpp.consumable_id
					RIGHT JOIN rl_address_tenders as at on at.address_id = ap.address_id and at.id = atp.tender_id 
					WHERE p.id = $id
					AND at.address_id = $address
					AND atp.tender_id IS NOT NULL
					GROUP BY at.id 
					) as tmp
					GROUP BY month 
					HAVING month IS NOT NULL";

		$query = $select.$queryTags.$sql;

		// if parameters is not empty, let's set them
        if(!empty($paramsArr)) {
	        $query = DB::raw( $query );

            // several lines will be drown
            $singleChart = false;
        } else {
            $query = DB::raw( $query );

            // only one line will be drown on chart
            $singleChart = true;
        }

		$result = DB::select($query);

		$responsData = [];

		$arrayTotal = array_column( $result, 'total' );

        $minTotal = 0;

		if(!empty($arrayTotal)) {
            $minTotal = min( $arrayTotal );
        }

		$delimetr    = 1;
		$delimetrKey = 'R';

		$date = '';

		if ( $minTotal < 100000000 ) {

			$delimetr = 1000;

			$delimetrKey = 'K';

		}elseif ( $minTotal < 100000000000 ) {

			$delimetr = 1000000;

			$delimetrKey = 'M';
		}

		foreach ( $result as $i => $row ) {

			$responsData[ $i ] = [];

			foreach ( $row as $j => $value ) {

				if ( $j == 'month' ) {

					$date = $value;

					$responsData[ $i ][] = $date;

				} elseif ( $j == 'total' ) {

					$total = $value / $delimetr;

					$responsData[ $i ][] = $total;

					$responsData[ $i ][] = '<span class="tooltip-total">' . $date
					                       . '<br>Total: ' . number_format($total) . $delimetrKey . '</span>';
				} else {

					$responsData[ $i ][] = intval( $value / $delimetr );
				}
			}
		}

		return response()->json(
		    [
		        'chartsData'  => $responsData,
			    200,
			    [],
			    JSON_NUMERIC_CHECK,
                'delimetrKey' => $delimetrKey,
                'singleChart' => $singleChart,
	    	]
        );
	}

    function getProductConsumableSum($addressId, $productId) {

            $sql = "SELECT SUM(total_price) as total_price, name 
                    from (
                        SELECT at.id, atp.id as atpID, MAX(atp.total_price) as total_price, pc.name
                        FROM rl_address_tenders AS at
                        LEFT JOIN rl_address_tenders_purchase_products AS atpp
                            ON atpp.tender_id = at.id
                        LEFT JOIN rl_address_tenders_purchase AS atp
                            ON atp.tender_id = at.id
                        LEFT JOIN rl_product_consumables AS pc
                            ON pc.id = atpp.consumable_id
                        WHERE product_id = $productId
                        AND address_id = $addressId
                        AND atpp.consumable_id IS NOT NULL
                        AND YEAR(NOW()) - 1 = YEAR(at.tender_date)
                        GROUP BY at.id, atpID 
                    )
                     subtbl";

	    $res = DB::select(DB::raw($sql));

	    $data = $res && $res[0] ? $res[0] : false;

	    return response()->json($data);

    }


    function getProductListForAddress (Address $address)
    {
        $sql = "SELECT p.id, p.company, p.name
            FROM rl_products AS p
            LEFT JOIN rl_address_tenders_purchase_products AS atpp
                ON atpp.product_id = p.id
            LEFT JOIN rl_address_tenders AS at
                ON at.id = atpp.tender_id
            WHERE at.address_id = $address->id
            GROUP BY p.id";

	    $products = DB::select(DB::raw($sql));

	    return response()->json($products);
    }


    function getUsedConsumablesByAddressAndProduct()
    {
        $prodId = request()->get('product_id');
        $addressId = request()->get('address_id');

        $sql = "select atpp.consumable_id as id, pc.name
            from rl_address_tenders_purchase_products as atpp
            left join rl_address_tenders as at 
              on at.id = atpp.tender_id
            left join rl_product_consumables as pc 
              on pc.id = atpp.consumable_id
            where atpp.product_id = ?
            and at.address_id = ?
            and atpp.consumable_id IS NOT NULL
            group by atpp.consumable_id";

        $tags = DB::select(DB::raw($sql), [$prodId, $addressId]);


        $tagsWithColor = [];

        foreach ( $tags as $tag ) {
            $tag->color  = sprintf( '#%02X%02X%02X', rand( 0, 255 ), rand( 0, 255 ), rand( 0, 255 ) );
            $tagsColor[] = $tag;
        }

        return response()->json($tagsWithColor);
    }

}
