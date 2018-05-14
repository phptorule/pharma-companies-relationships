<?php

namespace App\Http\Controllers;


use App\Models\Tender;
use Illuminate\Http\Request;

class TendersController extends Controller {
	function getTendersByAddress( $id ) {
		$tenders = Tender::where( 'address_id', $id )
		                 ->with( [
			                 'purchase' => function ( $q ) {
				                 $q->orderBy( 'total_price', 'desc' )->take( 3 );
			                 }
		                 ] )
		                 ->with( 'budget' )
		                 ->get();

		$actualDate = date( "Y" );

		$amountOldYear    = null;
		$amountActualYear = null;
		$amountNextYear   = null;

		$rateOldYear    = null;
		$rateActualYear = null;
		$rateNextYear   = null;


		foreach ( $tenders as $tender ) {
			$tenderDate = date( "Y", strtotime( $tender->tender_date ) );

			if ( isset( $tender->budget[0]->delivery_year ) ) {

				foreach ( $tender->budget as $budget ) {

					$deliveryDate = intval( $budget->delivery_year );

				}

				if ( $actualDate > $deliveryDate ) {

					$amountOldYear += $budget->budget;

				} else if ( $actualDate == $deliveryDate ) {

					$amountActualYear += $budget->budget;

				} else if ( $actualDate < $deliveryDate ) {

					$amountNextYear += $budget->budget;

				}
			} else if ( $tender->budgeted_cost != $tender->actual_cost && $tender->actual_cost != null && $tender->tender_date != null ) {
				if ( $actualDate > $tenderDate ) {

					$amountOldYear += $tender->actual_cost;

				} else if ( $actualDate == $tenderDate ) {

					$amountActualYear += $tender->actual_cost;

				} else if ( $actualDate < $tenderDate ) {

					$amountNextYear += $tender->actual_cost;

				}
			}

			if ( $tender->budgeted_cost != null && $tender->tender_date != null ) {
				if ( $actualDate > $tenderDate ) {

					$amountOldYear += $tender->budgeted_cost;

				} else if ( $actualDate == $tenderDate ) {

					$amountActualYear += $tender->budgeted_cost;

				} else if ( $actualDate < $tenderDate ) {

					$amountNextYear += $tender->budgeted_cost;

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
}
