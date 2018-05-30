<?php

namespace App\Http\Controllers;


use App\Models\Tender;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TendersController extends Controller {
	function getTendersByAddress( $id ) {

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
}
