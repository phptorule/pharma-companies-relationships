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

	public function purchaseByTenders($id)
	{
		$product = DB::table('rl_address_tenders_purchase')
		             ->where('rl_address_tenders_purchase_products.product_id',$id)
		             ->join('rl_address_tenders_purchase_products', 'rl_address_tenders_purchase_products.purchase_id', '=', 'rl_address_tenders_purchase.id')
		             ->join('rl_address_tenders', 'rl_address_tenders.id', '=', 'rl_address_tenders_purchase.tender_id')
		             ->get();
		return response()->json($product);
	}
}
