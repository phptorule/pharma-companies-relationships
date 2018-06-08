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

		$select = "SELECT
						((YEAR(CURDATE()) - YEAR(MIN(tdate))+1)) as last_tender_date,
						MAX(budgetedCost) as max_total_spent,
						MIN(budgetedCost) as min_total_spent,
						SUM(budgetedCost) as total_budgeted,
						SUM(atbBudget) as next_budgeted_cost,
						GROUP_CONCAT(DISTINCT tags SEPARATOR ', ') as tag_ids
						FROM
						(
						    SELECT at.tender_date as tdate,
                            atpp.consumable_id as tags,
							atb.budget as atbBudget,
						    at.budgeted_cost as budgetedCost
							FROM rl_products as p
							LEFT JOIN rl_address_tenders_purchase_products AS atpp ON atpp.product_id = p.id
							LEFT JOIN rl_address_tenders_purchase AS atp ON atp.id = atpp.purchase_id
							LEFT JOIN rl_address_products AS ap ON ap.product_id = p.id
                            LEFT JOIN rl_product_consumables AS pc ON pc.id = atpp.consumable_id
							RIGHT JOIN rl_address_tenders as at ON (at.address_id = ap.address_id and at.id = atp.tender_id)
							LEFT JOIN rl_address_tenders_budgets as atb ON atb.tender_id = at.id
							WHERE p.id = :id1
							AND at.address_id = :address1
							AND at.id IS NOT NULL
                            GROUP BY at.id
						) as product
		";

		$tenders = DB::select(
            DB::raw( $select ),
            [
                'id1' => $id,
                'address1' => $address,

            ]
        );

		if ( $tenders ) {
			foreach ( $tenders as $tender ) {
				$tenderdata = $tender;
			}

			return response()->json( $tenderdata );
		}

		return response()->json( [
			'last_tender_date'    => null,
			'max_total_spent'     => null,
			'min_total_spent'     => null,
			'total_budgeted'      => null,
			'last_budgeted_cost'  => null,
			'first_budgeted_cost' => null,
			'tag_ids'             => null
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
        $result = \Illuminate\Support\Facades\Cache::remember(
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

		return response()->json( $tagsColor );
	}

	public function TenderByProductChart( $id, $address ) {
		$select = 'DATE_FORMAT(at.tender_date, \'%Y/%m/%d\') as month, SUM(at.budgeted_cost) as total';

		// init array of parameters
        $paramsArr = [];

		if ( isset( request()->tags ) && $tags = request()->tags) {
		    if(!empty($tags) && is_array($tags)){
                foreach ( $tags as $tag ) {

                    // clear tag as get intval of it
                    $tag = intval($tag);

                    $select .= ", ( 
                    SELECT SUM(at$tag.budgeted_cost) 
                    FROM rl_products AS p$tag
                    LEFT JOIN rl_address_tenders_purchase_products AS atpp$tag ON atpp$tag.product_id = p$tag.id
					LEFT JOIN rl_address_tenders_purchase AS atp$tag ON atp$tag.id = atpp$tag.purchase_id
					LEFT JOIN rl_product_consumables AS pc$tag ON pc$tag.id = atpp$tag.consumable_id
					LEFT JOIN rl_address_products AS ap$tag ON ap$tag.product_id = p$tag.id 
					RIGHT JOIN rl_address_tenders AS at$tag ON (at$tag.address_id = ap$tag.address_id and at$tag.id = atp$tag.tender_id)
                    WHERE p$tag.id = ?
                    AND pc$tag.id = $tag
                    AND at$tag.address_id = ?
                    AND month = DATE_FORMAT(at$tag.tender_date, '%Y/%m/%d')
                    GROUP BY month
                    
                     ) as tag$tag";

                    // put data for placeholders to array of parameters
                    array_push($paramsArr, $id, $address);
                }
            }
		}

		$query = DB::table( 'rl_products AS p' )
           ->where( 'p.id', $id )
           ->where( 'at.address_id', $address )
           ->whereNotNull( 'atp.tender_id' )
           ->leftJoin( 'rl_address_tenders_purchase_products AS atpp', 'atpp.product_id', '=', 'p.id' )
           ->leftJoin( 'rl_address_tenders_purchase AS atp', 'atp.id', '=', 'atpp.purchase_id' )
			->leftJoin( 'rl_address_products AS ap', 'ap.product_id', '=', 'p.id' )
           ->leftJoin( 'rl_product_consumables AS pc', 'pc.id', '=', 'atpp.consumable_id' )
			->rightJoin( 'rl_address_tenders AS at', function($q)
			{
				$q->on('at.address_id', '=', 'ap.address_id')
				  ->on('at.id', '=', 'atp.tender_id');
			})
           ->groupBy( 'month' )
           ->havingRaw( 'month IS NOT NULL' );

		// if parameters is not empty, let's set them
        if(!empty($paramsArr)) {
            $query->selectRaw(
                $select,
                $paramsArr
            );

            // several lines will be drown
            $singleChart = false;
        } else {
            $query->select(
                DB::raw( $select )
            );

            // only one line will be drown on chart
            $singleChart = true;
        }

		$result = $query->get()->toArray();

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

					$responsData[ $i ][] = intval( $value );
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

}
