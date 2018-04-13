<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Product;
use App\Models\Tag;
use Illuminate\Http\Request;

class AddressesController extends Controller
{


    function index()
    {
        $addresses = Address::all();

        return response()->json($addresses);
    }


    function loadAddressesPaginated()
    {
        $addresses = Address::with(['tags' => function ($q){
            $q->select(['id', 'name']);
        }])
            ->with('cluster')
            ->paginate(20);

        return response()->json($addresses);
    }


    function loadFilterValues()
    {
        $tags = Tag::get(['id', 'name']);
        $products = Product::get(['id', 'name']);

        $filters = [
            'tag_list' => $tags,
            'used_product_list' => $products
        ];

        return response()->json($filters);
    }
}
