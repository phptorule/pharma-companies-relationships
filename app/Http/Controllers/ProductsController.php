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

		$joinTable = "LEFT JOIN rl_address_tenders_purchase AS atp ON atp.tender_id = at.id
						LEFT JOIN rl_address_tenders_purchase_products AS atpp ON atpp.purchase_id = atp.id
						LEFT JOIN rl_products AS p ON p.id = atpp.product_id";

		$select = "SELECT
						((YEAR(CURDATE()) - YEAR(MIN(at.tender_date))+1)) as last_tender_date,
						MAX(at.budgeted_cost) as max_total_spent,
						MIN(at.budgeted_cost) as min_total_spent,
						(
						SELECT SUM(atp.total_price) as total_spent
						
						FROM rl_products as p
						LEFT JOIN rl_address_tenders_purchase_products AS atpp ON atpp.product_id = p.id
						LEFT JOIN rl_address_tenders_purchase AS atp ON atp.id = atpp.purchase_id
						LEFT JOIN rl_address_tenders AS at ON at.id = atp.tender_id
						LEFT JOIN rl_address_products AS ap ON ap.product_id = p.id
						LEFT JOIN rl_addresses AS a ON ap.address_id = a.id
						WHERE p.id = :id1
						AND at.address_id = :address1
						AND YEAR(CURDATE()) - YEAR(at.tender_date) <= 2
						GROUP BY p.id
						ORDER BY total_spent DESC
						LIMIT 1) as total_budgeted,
						
						(SELECT SUM(at.budgeted_cost)
						FROM rl_address_tenders as at
						$joinTable
						WHERE p.id = :id2
						AND at.address_id = :address2
						AND YEAR(at.tender_date) <= YEAR(CURDATE())
						) as last_budgeted_cost,
						
						(SELECT SUM(at.budgeted_cost)
						FROM rl_address_tenders as at
						$joinTable
						WHERE p.id = :id3
						AND at.address_id = :address3
						AND YEAR(at.tender_date) >= YEAR(CURDATE())
						) as first_budgeted_cost,
						
						GROUP_CONCAT(DISTINCT atpp.consumable_id SEPARATOR ', ') as tag_ids
						
						FROM rl_products as p
						LEFT JOIN rl_address_tenders_purchase_products AS atpp ON atpp.product_id = p.id
						LEFT JOIN rl_address_tenders_purchase AS atp ON atp.id = atpp.purchase_id
						LEFT JOIN rl_address_tenders AS at ON at.id = atp.tender_id
						LEFT JOIN rl_product_consumables AS pc ON pc.id = atpp.consumable_id
						AND YEAR(CURDATE()) - YEAR(at.tender_date) <= 2
						WHERE p.id = :id4
						AND at.address_id = :address4
						GROUP BY p.id";

		$tenders = DB::select(
            DB::raw( $select ),
            [
                'id1' => $id,
                'address1' => $address,
                'id2' => $id,
                'address2' => $address,
                'id3' => $id,
                'address3' => $address,
                'id4' => $id,
                'address4' => $address,
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

	public function loadTopProducts( $address ) {

		$sql = "SELECT 
				p.*, p.id as prod_id, 
				SUM(atp.total_price) as total_spent, 
				SUM(atp.quantity) as volume, GROUP_CONCAT(DISTINCT atp.id SEPARATOR ', ') as purchase_ids, 
				MAX(atp.units) as unit, 
				DATE_FORMAT(MAX(at.tender_date), '%d-%m-%Y') as last_tender_date 
				FROM rl_products as p 
				LEFT JOIN rl_address_tenders_purchase_products AS atpp ON atpp.product_id = p.id 
				LEFT JOIN rl_address_tenders_purchase AS atp ON atp.id = atpp.purchase_id 
				LEFT JOIN rl_address_tenders AS at ON at.id = atp.tender_id 
				LEFT JOIN rl_address_products AS ap ON ap.product_id = p.id 
				LEFT JOIN rl_addresses AS a ON at.address_id = a.id 
				WHERE at.address_id = :address 
				AND YEAR(CURDATE()) - YEAR(at.tender_date) <= 2 
				GROUP BY p.id 
				ORDER BY total_spent DESC LIMIT 3";

		$result = DB::select(
		    DB::raw( $sql ),
            ['address' => $address]
        );

		return response()->json( $result );
	}

	public function getProductByTendersPaginated( $id ) {
		$select = 'at.id as tender_id, at.address_id, at.budgeted_cost, at.actual_cost, at.url as tender_url, at.tender_date,
					atb.budget,
					atp.id as purchase_id, atp.quantity as purchase_quantity, atp.total_price as purchase_total_price,
					atp.name as purchase_name,atp.remark as purchase_remark,
					pc.name as tag_name, pc.id as tag_id,
					ats.amount as suppliers_amount,
					s.name as suppliers_name, s.address as suppliers_address';

		$query = $this->prepareTendersQuery( $id, $select );

		$tenders = $query->paginate( 5 );

		return response()->json( $tenders );
	}

	public function productByAddressPaginated( Address $address ) {

		$products = Product::whereHas( 'addresses', function ( $q ) use ( $address ) {
			return $q->where( 'id', $address->id );
		} )
		                   ->paginate( 10 );

		return response()->json( $products );
	}

	public function getProductByTendersToExcel( $id ) {

		$select = 'at.budgeted_cost, at.tender_date,
		           atpp.product_id as tpp_product_id, atpp.consumable_id as tpp_consumable_id,
		           atp.quantity as purchase_quantity, atp.total_price as purchase_total_price, atp.name as purchase_name,
		           pc.name as tag_name, pc.id as tag_id,
		           p.name as product_name';

		$query = $this->prepareTendersQuery( $id, $select );

		$tenders = $query->get();

		return response()->json( $tenders );
	}

	public function prepareTendersQuery( $id, $select ) {

		$query = $this->getTendersByProduct( $id, $select );

		$query = $this->composeConditions( $query, request()->all() );

		return $query;
	}

	public function composeConditions( $query, $requestParams ) {

		if ( isset( $requestParams['sort-by'] ) ) {

			$field     = explode( '-', $requestParams['sort-by'] )[0];
			$direction = explode( '-', $requestParams['sort-by'] )[1];

			if ( $field == 'budget' ) {
				$field = 'budgeted_cost';
			} else if ( $field == 'date' ) {
				$field = 'tender_date';
			} else if ( $field == 'tenders' ) {
				$field = 'at.id';
			}

			$query->orderBy( $field, $direction );
		}

		if ( isset( $requestParams['min'] ) && isset( $requestParams['max'] ) ) {
			$query->where( 'budgeted_cost', '>=', $requestParams['min'] )
			      ->where( function ( $q ) use ( $requestParams ) {
				      $q->where( 'budgeted_cost', '<=', $requestParams['max'] );
			      } );
		}

		if ( isset( $requestParams['tenders-search'] ) ) {
			$query->where( 'atp.name', 'LIKE', '%' . $requestParams['tenders-search'] . '%' );
		}

		if ( isset( $requestParams['tag-cons'] ) ) {
			$query->whereIn( 'consumable_id', $requestParams['tag-cons'] );
		}

		return $query;
	}

	public function getTendersByProduct( $id, $select ) {
		$query = DB::table( 'rl_address_tenders AS at' )
		           ->select( DB::raw( $select ) )
		           ->where( 'p.id', $id )
		           ->whereNotNull( 'atp.tender_id' )
		           ->whereNotNull( 'atb.tender_id' )
		           ->leftJoin( 'rl_address_tenders_purchase AS atp', 'atp.tender_id', '=', 'at.id' )
		           ->leftJoin( 'rl_address_tenders_budgets AS atb', 'atb.tender_id', '=', 'at.id' )
		           ->leftJoin( 'rl_address_tenders_purchase_products AS atpp', 'atpp.purchase_id', '=', 'atp.id' )
		           ->leftJoin( 'rl_product_consumables AS pc', 'atpp.consumable_id', '=', 'pc.id' )
		           ->leftJoin( 'rl_address_tenders_suppliers AS ats', 'ats.tender_id', '=', 'at.id' )
		           ->leftJoin( 'rl_suppliers AS s', 's.id', '=', 'ats.supplier_id' )
		           ->leftJoin( 'rl_products AS p', 'p.id', '=', 'atpp.product_id' );

		return $query;
	}

	public function getTendersByAddress( $id ) {
		$query = DB::table( 'rl_address_tenders AS at' )
		           ->select( DB::raw( 'at.id as tender_id, at.address_id, at.budgeted_cost, at.actual_cost, at.url as tender_url, at.tender_date,
		           atpp.*,
		           atp.id as purchase_id, atp.quantity as purchase_quantity, atp.total_price as purchase_total_price,
		           atp.name as purchase_name, atp.taxonomy as purchase_taxonomy, atp.remark as purchase_remark,
		           pc.name as tag_name, pc.id as tag_id, atb.*, 
		           p.id as product_id, p.name as product_name, p.company as product_company, p.description as product_description,
		           p.image as product_image' ) )
		           ->where( 'at.address_id', $id )
		           ->leftJoin( 'rl_address_tenders_purchase AS atp', 'atp.tender_id', '=', 'at.id' )
		           ->leftJoin( 'rl_address_tenders_budgets AS atb', 'atb.tender_id', '=', 'at.id' )
		           ->leftJoin( 'rl_address_tenders_purchase_products AS atpp', 'atpp.purchase_id', '=', 'atp.id' )
		           ->leftJoin( 'rl_product_consumables AS pc', 'atpp.consumable_id', '=', 'pc.id' )
		           ->leftJoin( 'rl_products AS p', 'p.id', '=', 'atpp.product_id' );

		return $query;
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
		$select = 'DATE_FORMAT(at.tender_date, \'%Y/%m\') as month, SUM(at.budgeted_cost) as total';

		// init array of parameters
        $paramsArr = [];

		if ( isset( request()->tags ) && $tags = request()->tags) {
		    if(!empty($tags) && is_array($tags)){
                foreach ( $tags as $tag ) {

                    // clear tag as get intval of it
                    $tag = intval($tag);

                    $select .= ", ( 
                    SELECT SUM(at$tag.budgeted_cost) 
                    FROM rl_address_tenders as at$tag
                    LEFT JOIN rl_address_tenders_purchase AS atp$tag ON atp$tag.tender_id = at$tag.id
                    LEFT JOIN rl_address_tenders_purchase_products AS atpp$tag ON atpp$tag.purchase_id = atp$tag.id
                    LEFT JOIN rl_product_consumables AS pc$tag ON atpp$tag.consumable_id = pc$tag.id
                    LEFT JOIN rl_products AS p$tag ON p$tag.id = atpp$tag.product_id
                    WHERE p$tag.id = ?
                    AND pc$tag.id = $tag
                    AND at$tag.address_id = ?
                    AND month = DATE_FORMAT(at$tag.tender_date, '%Y/%m')
                    GROUP BY month
                    
                     ) as tag$tag";

                    // put data for placeholders to array of parameters
                    array_push($paramsArr, $id, $address);
                }
            }
		}

		$query = DB::table( 'rl_address_tenders AS at' )
           ->where( 'p.id', $id )
           ->where( 'at.address_id', $address )
           ->whereNotNull( 'atp.tender_id' )
           ->leftJoin( 'rl_address_tenders_purchase AS atp', 'atp.tender_id', '=', 'at.id' )
           ->leftJoin( 'rl_address_tenders_purchase_products AS atpp', 'atpp.purchase_id', '=', 'atp.id' )
           ->leftJoin( 'rl_product_consumables AS pc', 'atpp.consumable_id', '=', 'pc.id' )
           ->leftJoin( 'rl_products AS p', 'p.id', '=', 'atpp.product_id' )
           ->groupBy( 'month' )
           ->havingRaw( 'month IS NOT NULL' );

		// if parameters is not empty, let's set them
        if(!empty($paramsArr)) {
            $query->selectRaw(
                $select,
                $paramsArr
            );
        } else {
            $query->select(
                DB::raw( $select )
            );
        }

		$result = $query->get()->toArray();

		$responsData = [];

		$arrayTotal = array_column( $result, 'total' );

		$minTotal = min( $arrayTotal );

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

					$total = intval( $value / $delimetr );

					$responsData[ $i ][] = $total;

					$responsData[ $i ][] = '<span class="tooltip-total">' . $date
					                       . '<br>Total: ' . $total . $delimetrKey . '</span>';
				} else {

					$responsData[ $i ][] = intval( $value );
				}
			}
		}

		return response()->json( [ 'chartsData'  => $responsData,
			200,
			[],
			JSON_NUMERIC_CHECK,
			                       'delimetrKey' => $delimetrKey
		] );
	}

}
