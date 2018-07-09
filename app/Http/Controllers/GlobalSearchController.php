<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\People;
use App\Models\Product;
use Illuminate\Http\Request;

class GlobalSearchController extends Controller
{


    function index()
    {
        $searchStr = request()->get('search');

        $countOrganisations = Address::where('name', 'like', '%'.$searchStr.'%')->count();

        $countAddresses = Address::where('address', 'like', '%'.$searchStr.'%')->count();

        $countPeople = People::where('name', 'like', '%'.$searchStr.'%')->count();

        $countProduct = Product::where('company', 'like', '%'.$searchStr.'%')->orWhere('name', 'like', '%'.$searchStr.'%')->count();


        $responseData = [
            'Organisation' => $countOrganisations,
            'Address' => $countAddresses,
            'Person' => $countPeople,
            'Product' => $countProduct,
        ];

        return response()->json($responseData);
    }


}
