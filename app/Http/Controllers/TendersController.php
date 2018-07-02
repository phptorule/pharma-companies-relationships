<?php

namespace App\Http\Controllers;

use App\Models\Tender;
use App\Models\TenderPurchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TendersController extends Controller {


    function show(Tender $tender) {

        $purchases = TenderPurchase::where('tender_id', $tender->id)
                        ->with(['products' => function($q){
                            $q->groupBy('rl_address_tenders_purchase_products.purchase_id', 'rl_products.id');
                        }])
                        ->with('consumables')
                        ->paginate(10);

        $responseData = [
            'tender'=>$tender,
            'purchases'=>$purchases
        ];

        return response()->json($responseData);

    }


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

		$tenders = $query->paginate( 10 );

		$tenders = $this->splitSuppliers($tenders);

		return response()->json( $tenders );

	}

	public function getTendersByAdderssPaginated($address) {

		$query = $this->prepareTendersQuery($address);

        $tenders = $query->paginate( 10 );

		$tenders = $this->splitSuppliers($tenders);

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

        $tenders = $this->replaceNullToEmptyStr($tenders->toArray());

		return response()->json( $tenders );
	}


	protected function replaceNullToEmptyStr($arr) {

	    $replacedArr = [];

	    foreach ($arr as $i => $row) {

            $replacedArr[$i] = [];

            foreach ($row as $prop => $val) {
                $replacedArr[$i][$prop] = is_null($val) ? '' : $val;
            }
        }

        return $replacedArr;
    }

	public function prepareTendersQuery($address, $id = '') {

		$select = "at.id as tender_id, 
					at.address_id, 
					at.budgeted_cost, 
					at.actual_cost, 
					at.url as tender_url, 
					at.tender_date, 
					atb.budget,
					(SELECT MAX(at.budgeted_cost) FROM rl_address_tenders as at where at.address_id = $address) as max_budgeted, 
					(SELECT MIN(at.budgeted_cost) FROM rl_address_tenders as at where at.address_id = $address) as min_budgeted,
					atp.id as purchase_id,
					atp.quantity as purchase_quantity,
					atp.total_price as purchase_total_price,
					atp.name as purchase_name,
					atp.remark as purchase_remark,
					#pc.name as tag_name,
					GROUP_CONCAT(DISTINCT(pc.name) SEPARATOR ', ') as tag_name,
					pc.id as tag_id,
					IF(p.name='',CONCAT(p.company, ': unspecified'), p.name) as product_name,
					group_concat(DISTINCT(ats.amount) ORDER BY ats.amount DESC separator ',') AS suppliers_amount,
					count(s.name) as winner,
					group_concat(DISTINCT(CONCAT(s.name, ': ', ats.amount)) ORDER BY ats.amount DESC SEPARATOR ', ') as supplier_winner,
					group_concat(DISTINCT(IF(ISNULL(s.address),s.name,CONCAT(s.name,' - ', s.address))) ORDER BY ats.amount DESC separator ';') AS suppliers_data";


        $query = DB::table( 'rl_address_tenders AS at' )
		           ->select( DB::raw( $select ) )
		           ->where( 'at.address_id', $address )
                    ->leftJoin( 'rl_address_tenders_purchase AS atp', 'atp.tender_id', '=', 'at.id' )
					->leftJoin( 'rl_address_tenders_purchase_products AS atpp', 'atpp.purchase_id', '=', 'atp.id' )
                    ->leftJoin( 'rl_products AS p', 'p.id', '=', 'atpp.product_id')
					->leftJoin( 'rl_address_products AS ap', 'ap.product_id', '=', 'p.id' )
					->leftJoin( 'rl_product_consumables AS pc', 'pc.id', '=', 'atpp.consumable_id' )
					->leftJoin( 'rl_address_tenders_budgets AS atb', 'atb.tender_id', '=', 'at.id' )
		           ->leftJoin( 'rl_address_tenders_suppliers AS ats', 'ats.tender_id', '=', 'at.id' )
		           ->leftJoin( 'rl_suppliers AS s', 's.id', '=', 'ats.supplier_id' )
					->groupBy('at.id');

		if($id != ''){$query->where( 'p.id', $id );}

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

	public function splitSuppliers($tenders){

		foreach ($tenders as $tender){

			$suppliers_amount = explode(',', $tender->suppliers_amount);

			$suppliers_data = explode(';', $tender->suppliers_data);

			$suppliers = [];

			for($i = 0;$i < count($suppliers_amount);){

				if(intval($suppliers_amount[$i]) != 0){

					$suppliers[$i][] = $suppliers_data[$i];

					$suppliers[$i][] = $suppliers_amount[$i];
				}

				$i++;
			}

			$tender->suppliers_data = $suppliers;

		}

		return $tenders;
	}


    function queryDefinedProductIds($addressId, $usedProductsIds)
    {
        if(is_null($usedProductsIds)) {
            return [];
        }

        $sql = "SELECT 
                    atpp.product_id,
                    CONCAT(p.company, ': ', IF(p.name != '', p.name, 'unspecified')) as product_name
                FROM rl_address_tenders AS at
                LEFT JOIN rl_address_tenders_purchase_products AS atpp
                    ON atpp.tender_id = at.id
                LEFT JOIN rl_products AS p
                    ON p.id = atpp.product_id
                WHERE at.address_id = $addressId
                AND atpp.product_id IS NOT NULL
                AND atpp.product_id IN ($usedProductsIds)
                GROUP BY atpp.product_id
                ORDER BY atpp.product_id ASC";

        return DB::select(DB::raw($sql));
    }


    function search($items, $needle, $propName) {
        foreach ($items as $i => $item) {
            if($item->$propName == $needle) {
                return $i;
            }
        }

        return null;
    }


    function queryChartDataForProducts($addressId, $usedProductsIds)
    {
        if(is_null($usedProductsIds)) {
            $usedProductsIds = -1;
        }

        $sql = "SELECT 
                    atpp.product_id,
                    SUM(at.budgeted_cost) AS tender_total_budget,
                    SUM(atp.total_price) AS purchases_total_price, 
                    (SUM(at.budgeted_cost) - SUM(atp.total_price)) AS other,
                    DATE_FORMAT(at.tender_date, '%d/%m/%Y') as tender_date
                FROM rl_address_tenders AS at
                LEFT JOIN rl_address_tenders_purchase AS atp
                    ON atp.tender_id = at.id
                LEFT JOIN rl_address_tenders_purchase_products AS atpp
                    ON atpp.tender_id = at.id
                WHERE at.address_id = $addressId
                AND (atpp.product_id IN ($usedProductsIds) OR atpp.product_id IS NULL)
                GROUP BY at.tender_date, atpp.product_id
                ORDER BY at.tender_date  ASC";

        return DB::select(DB::raw($sql));
    }


    function calculateDelimiterValues($values)
    {
        $maxVal = max($values);

        $delimiter = 1;
        $key = '';

        if ($maxVal < 100000000) {
            $delimiter = 1000;
            $key = 'K';
        }
        elseif ($maxVal < 100000000000) {
            $delimiter = 1000000;
            $key = 'M';
        }

        return [
            'key' => $key,
            'delimiter' => $delimiter
        ];
    }


	function getGraphDataForProductsByTendersAndAddress($address)
    {
        $requestParams = request()->all();
        $usedProductIds = isset($requestParams['used-products']) ? $requestParams['used-products'] : null;

        $sqlResults = $this->queryChartDataForProducts($address, $usedProductIds);

        $delimiterValues = $this->calculateDelimiterValues(array_column($sqlResults, 'purchases_total_price'));

        $definedProductIds = $this->queryDefinedProductIds($address, $usedProductIds);

        $titles = [];
        $preData = [];

        foreach ($sqlResults as $i => $result) {
            if($requestParams['include-others'] == 1) {
                $preData[$result->tender_date] = array_fill(0, count($definedProductIds) + 2, 0);
            }
            else {
                $preData[$result->tender_date] = array_fill(0, count($definedProductIds) + 1, 0);
            }
        }

        foreach ($sqlResults as $i => $result) {

            $preData[$result->tender_date][0] = $result->tender_date;

            if($requestParams['include-others'] == 1) {
                $preData[$result->tender_date][1] += round(floatval($result->other)/$delimiterValues['delimiter'], 2);
            }

            if(!is_null($index = $this->search($definedProductIds, $result->product_id, 'product_id'))) {

                $indexPlus2 = $index + 1;

                if($requestParams['include-others'] == 1) {
                    $indexPlus2 = $index + 2;
                }

                $preData[$result->tender_date][$indexPlus2] += round(floatval($result->purchases_total_price)/$delimiterValues['delimiter'], 2);
            }
        }

        foreach ($definedProductIds as $definedProduct) {
            $titles[] = $definedProduct->product_name;
        }

        if($requestParams['include-others'] == 1) {
            array_unshift($titles, 'Other');
        }

        array_unshift($titles, 'Date');

        $data = array_values($preData);

        array_unshift($data, $titles);

        return response()->json([
            'chart_data' => $data,
            'delimiter_key' => $delimiterValues['key']
        ], 200, [], JSON_NUMERIC_CHECK);
    }


    function getUsedConsumablesByAddressAndTender ($addressId) {

        $sql = "select atpp.consumable_id as id, pc.name
            from rl_address_tenders_purchase_products as atpp
            left join rl_address_tenders as at 
              on at.id = atpp.tender_id
            left join rl_product_consumables as pc 
              on pc.id = atpp.consumable_id
            where at.address_id = ?
            and atpp.consumable_id IS NOT NULL
            group by atpp.consumable_id";

        $tags = DB::select(DB::raw($sql), [$addressId]);

        return response()->json($tags);
    }
}
