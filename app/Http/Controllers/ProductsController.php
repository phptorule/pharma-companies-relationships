<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ProductsController extends Controller
{
    public function productByPurchase($id)
    {
	    $product = DB::table('rl_products')
            ->where('rl_address_tenders_purchase_products.purchase_id',$id)
		    ->join('rl_address_tenders_purchase_products', 'rl_address_tenders_purchase_products.product_id', '=', 'rl_products.id')
		    ->get();

	    return response()->json($product);
    }

	public function productByTenders($id)
	{
		$query = $this->getTenders($id);
		$tenders = $query->orderBy('tender_date', 'asc')->get();
		return response()->json($tenders);
	}

	public function getProductByTendersPaginated($id)
	{
		$query = $this->prepareTendersQuery($id);

		$tenders = $query->paginate(2);

		return response()->json($tenders);
	}

	public function prepareTendersQuery($id)
	{

		$query = $this->getTenders($id);

		$query = $this->composeConditions($query, request()->all());

		return $query;
	}

	public function composeConditions($query, $requestParams)
	{

		if (isset($requestParams['sort-by'])) {

			$field = explode('-',$requestParams['sort-by'])[0];
			$direction = explode('-',$requestParams['sort-by'])[1];

			if($field == 'budget') {
				$field = 'budgeted_cost';
			}
			else if($field == 'date') {
				$field = 'tender_date';
			}
			else if($field == 'tenders') {
				$field = 'rl_address_tenders.id';
			}

			$query->orderBy($field,$direction);
		}

		if (isset($requestParams['min'])&&isset($requestParams['max'])) {
			$query->where('budgeted_cost','>=',$requestParams['min'])
			->where(function ($q) use ($requestParams) {
				$q->where( 'budgeted_cost', '<=', $requestParams['max'] );
			});
		}
		if (isset($requestParams['tenders-search'])) {
			$query->where('rl_address_tenders_purchase.name', 'LIKE', '%'.$requestParams['tenders-search'].'%');
		}

		return $query;
	}

	public function getTenders($id)
	{
		$query = DB::table('rl_address_tenders_purchase')
		           ->where('rl_address_tenders_purchase_products.product_id',$id)
		           ->join('rl_address_tenders_purchase_products', 'rl_address_tenders_purchase_products.purchase_id', '=', 'rl_address_tenders_purchase.id')
		           ->join('rl_address_tenders', 'rl_address_tenders.id', '=', 'rl_address_tenders_purchase.tender_id')
		           ->join('rl_address_tenders_budgets', 'rl_address_tenders_budgets.id', '=', 'rl_address_tenders_purchase.tender_id');
		return $query;
	}

	function loadTagsValues($id)
	{

		$tags = DB::table('rl_product_consumables')
		           ->where('rl_address_tenders_purchase_products.purchase_id',$id)
		           ->join('rl_address_tenders_purchase_products', 'rl_address_tenders_purchase_products.consumable_id', '=', 'rl_product_consumables.id')
		           ->get(['rl_product_consumables.id', 'rl_product_consumables.name']);

		$filters = [
			'tag_list' => $tags
		];

		return response()->json($filters);
	}
}
