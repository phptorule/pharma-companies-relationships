<?php

namespace App\Http\Controllers;

use App\Models\Tender;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TendersController extends Controller {

	public function getTendersByAddress( $id ) {

        $actualDate = date( "Y" );

        $tenders = DB::table( 'rl_address_tenders_budgets AS atb' )
            ->select( DB::raw( 'delivery_year, sum(budget) as total' ) )
            ->where( 'at.address_id', $id )
            ->where( 'delivery_year', '>=', $actualDate -1 )
            ->where( 'delivery_year', '<=', $actualDate +1 )
            ->join( 'rl_address_tenders AS at', 'atb.tender_id', '=', 'at.id' )
            ->groupBy('delivery_year')
            ->get();

		$amountOldYear    = null;
		$amountActualYear = null;
		$amountNextYear   = null;

		$rateOldYear    = null;
		$rateActualYear = null;
		$rateNextYear   = null;

		foreach ( $tenders as $tender ) {
			if ( !empty( $tender->delivery_year ) ) {

                if ($tender->delivery_year == $actualDate) {
                    // this is current year
                    $amountActualYear = $tender->total;
                } elseif ($tender->delivery_year == $actualDate - 1) {
                    // this is old year (previous)
                    $amountOldYear = $tender->total;
                } elseif ($tender->delivery_year == $actualDate + 1) {
                    // this is next year
                    $amountNextYear = $tender->total;
                }
			}
		}

		if ( $amountOldYear != null && $amountNextYear != null ) {

			$rateOldYear = ( ( $amountOldYear / $amountNextYear ) - 1 ) * 100;

			$rateNextYear = ( ( $amountNextYear / $amountOldYear ) - 1 ) * 100;
		}

		return response()->json( [
			'amountOldYear'    => $amountOldYear,
			'amountActualYear' => $amountActualYear,
			'amountNextYear'   => $amountNextYear,
			'rateOldYear'      => $rateOldYear,
			'rateActualYear'   => $rateActualYear,
			'rateNextYear'     => $rateNextYear,
		] );
	}

	public function getTendersByProductAndAdderssPaginated($product, $address) {

		$query = $this->prepareTendersQuery($address, $product);

		$tenders = $query->paginate( 5 );

		return response()->json( $tenders );

	}

	public function getTendersByAdderssPaginated($address) {

		$query = $this->prepareTendersQuery($address);

		$tenders = $query->paginate( 5 );

		return response()->json( $tenders );
	}

	public function getTendersByProductAndAddressToExcel($product, $address) {

		$query = $this->prepareTendersQuery($address, $product);

		$tenders = $query->get();

		return response()->json( $tenders );
	}

	public function getTendersByAddressToExcel($address) {

		$query = $this->prepareTendersQuery($address);

		$tenders = $query->get();

		return response()->json( $tenders );
	}

	public function prepareTendersQuery($address, $id = '') {

		$select = 'at.id as tender_id, at.address_id, at.budgeted_cost, at.actual_cost, at.url as tender_url, at.tender_date,
					atb.budget,
					atp.id as purchase_id, atp.quantity as purchase_quantity, atp.total_price as purchase_total_price,
					atp.name as purchase_name,atp.remark as purchase_remark,
					pc.name as tag_name, pc.id as tag_id,
					ats.amount as suppliers_amount,
					s.name as suppliers_name, s.address as suppliers_address';

		$query = DB::table( 'rl_address_tenders AS at' )
		           ->select( DB::raw( $select ) )
		           ->where( 'p.id', $id )
		           ->where( 'at.address_id', $address )
		           ->whereNotNull( 'atp.tender_id' )
		           ->whereNotNull( 'atb.tender_id' )
		           ->leftJoin( 'rl_address_tenders_purchase AS atp', 'atp.tender_id', '=', 'at.id' )
		           ->leftJoin( 'rl_address_tenders_budgets AS atb', 'atb.tender_id', '=', 'at.id' )
		           ->leftJoin( 'rl_address_tenders_purchase_products AS atpp', 'atpp.purchase_id', '=', 'atp.id' )
		           ->leftJoin( 'rl_product_consumables AS pc', 'atpp.consumable_id', '=', 'pc.id' )
		           ->leftJoin( 'rl_address_tenders_suppliers AS ats', 'ats.tender_id', '=', 'at.id' )
		           ->leftJoin( 'rl_suppliers AS s', 's.id', '=', 'ats.supplier_id' )
		           ->leftJoin( 'rl_products AS p', 'p.id', '=', 'atpp.product_id' );

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

}
