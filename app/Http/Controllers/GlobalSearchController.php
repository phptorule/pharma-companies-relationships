<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GlobalSearchController extends Controller
{


    function index()
    {
        return response()->json('server data');
    }


}
