<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Product;
use App\Models\ProductConsumable;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProductsController extends Controller {

	public function productById( Product $product )
	{

		return response()->json( $product );

	}

	public function productByTenders( $id ) {

		if ( isset( request()->chart ) ) {
			$select = 'at.tender_date, pc.id as tag_id, at.budgeted_cost, pc.name as tag_name';
		} else {
			$select = 'at.id as tender_id, at.address_id, at.budgeted_cost, at.actual_cost, at.url as tender_url, at.tender_date,
		           atpp.product_id as product_id, atpp.consumable_id as tpp_consumable_id,
		           atp.id as purchase_id, atp.quantity as purchase_quantity, atp.total_price as purchase_total_price, atp.name as purchase_name, atp.remark as purchase_remark,
		           pc.name as tag_name, pc.id as tag_id,
                   atb.budget, atb.delivery_year';
		}

		$query = $this->getTendersByProduct( $id, $select );

		$tenders = $query->orderBy( 'tender_date', 'asc' )->get();

		return response()->json( $tenders );
	}

	public function addressByProducts( $id ) {
		$query   = $this->getTendersByAddress( $id );
		$tenders = $query->orderBy( 'tender_date', 'asc' )->get();

		return response()->json( $tenders );
	}

	public function loadTopProducts($address) {

		$sql = "SELECT p.*, p.id as prod_id, 
					SUM(atp.total_price) as total_spent, 
					SUM(atp.quantity) as volume, 
					GROUP_CONCAT(DISTINCT atp.id SEPARATOR ', ') as purchase_ids,
					MAX(at.tender_date) as last_tender_date
				
				FROM rl_products as p
				LEFT JOIN rl_address_tenders_purchase_products AS atpp ON atpp.product_id = p.id
				LEFT JOIN rl_address_tenders_purchase AS atp ON atp.id = atpp.purchase_id
				LEFT JOIN rl_address_tenders AS at ON at.id = atp.tender_id
				LEFT JOIN rl_address_products AS ap ON ap.product_id = p.id
				LEFT JOIN rl_addresses AS a ON ap.address_id = a.id
				WHERE a.id = $address
				AND YEAR(CURDATE()) - YEAR(at.tender_date) <= 2
				GROUP BY p.id
				ORDER BY total_spent DESC
				LIMIT 3";

		$result = DB::select(DB::raw($sql));

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

		return response()->json( $tags );
	}

	public function TenderByProductChart( $id ) {
		$select = 'DATE_FORMAT(at.tender_date, \'%Y/%m\') as month, SUM(at.budgeted_cost) as total';

		if ( isset( request()->tags ) && $tags = request()->tags ) {
			foreach ( $tags as $tag ) {
				$select .= ", ( 

						SELECT SUM(at$tag.budgeted_cost) 
						FROM rl_address_tenders as at$tag
						LEFT JOIN rl_address_tenders_purchase AS atp$tag ON atp$tag.tender_id = at$tag.id
						LEFT JOIN rl_address_tenders_purchase_products AS atpp$tag ON atpp$tag.purchase_id = atp$tag.id
						LEFT JOIN rl_product_consumables AS pc$tag ON atpp$tag.consumable_id = pc$tag.id
						LEFT JOIN rl_products AS p$tag ON p$tag.id = atpp$tag.product_id
						WHERE p$tag.id = $id
						AND pc$tag.id = $tag
						AND month = DATE_FORMAT(at$tag.tender_date, '%Y/%m')
						GROUP BY month
						
						 ) as tag$tag";
			}
		}

		$query = DB::table( 'rl_address_tenders AS at' )
		           ->select( DB::raw( $select ) )
		           ->where( 'p.id', $id )
		           ->whereNotNull( 'atp.tender_id' )
		           ->leftJoin( 'rl_address_tenders_purchase AS atp', 'atp.tender_id', '=', 'at.id' )
		           ->leftJoin( 'rl_address_tenders_purchase_products AS atpp', 'atpp.purchase_id', '=', 'atp.id' )
		           ->leftJoin( 'rl_product_consumables AS pc', 'atpp.consumable_id', '=', 'pc.id' )
		           ->leftJoin( 'rl_products AS p', 'p.id', '=', 'atpp.product_id' )
		           ->groupBy( 'month' )
		           ->havingRaw( 'month IS NOT NULL' );

		$result = $query->get();

		$responsData = [];

		foreach ( $result as $i => $row ) {
			$responsData[ $i ] = [];
			foreach ( $row as $j => $value ) {
				$responsData[ $i ][] = $j == 0 ? ( is_null( $value ) ? 0 : $value ) : intval( $value );
			}
		}


		return response()->json( $responsData, 200, [], JSON_NUMERIC_CHECK );
	}

}
