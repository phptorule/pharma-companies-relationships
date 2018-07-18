<?php

namespace App\Http\Controllers;

use App\Models\Configuration;
use Illuminate\Http\Request;

class ConfigurationsController extends Controller
{


    function index()
    {
        return response()->json(Configuration::all());
    }

}
