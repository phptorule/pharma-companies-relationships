<?php

namespace App\Http\Controllers;


use App\Models\Tender;
use Illuminate\Http\Request;

class TendersController extends Controller
{
	function getTendersByAddress($id)
	{
		$tenders = Tender::where('address_id', $id)
		                 ->with(['purchase' => function ($q){
			                 $q->orderBy('total_price', 'desc')->take(3);
		                 }])
		                 ->with('budget')
		                 ->get();

		return response()->json($tenders);
	}
}
