<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Tender;
use App\Models\TenderPurchase;
use Illuminate\Http\Request;

class ProductController extends Controller
{
	function show($id)
	{
		$tenders = Tender::where('address_id', $id)->get();
		$tenders->load('purchase', 'budget');
		return response()->json($tenders);
	}
}
