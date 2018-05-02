<?php

namespace App\Http\Controllers;

use App\Models\ProductTag;
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
				$field = 'at.id';
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
			$query->where('atp.name', 'LIKE', '%'.$requestParams['tenders-search'].'%');
		}

		if (isset($requestParams['tag-cons'])) {
			$query->whereIn('consumable_id', $requestParams['tag-cons']);
		}

		return $query;
	}

	public function getTenders($id)
	{
		$query = DB::table('rl_address_tenders_purchase AS atp')
					->select(DB::raw('atp.*, atpp.*, atp.*, at.*, pc.name as tag_name, pc.id as tag_id, atb.*'))
		           ->where('atpp.product_id',$id)
		           ->leftJoin('rl_address_tenders_purchase_products AS atpp', 'atpp.purchase_id', '=', 'atp.id')
		           ->leftJoin('rl_address_tenders AS at', 'at.id', '=', 'atp.tender_id')
		           ->leftJoin('rl_product_consumables AS pc', 'atpp.consumable_id', '=', 'pc.id')
		           ->leftJoin('rl_address_tenders_budgets AS atb', 'atb.id', '=', 'atp.tender_id');
		return $query;
	}

	public function loadTagsValues()
	{

		$tags = ProductTag::get(['id', 'name']);

		return response()->json($tags);
	}
}
