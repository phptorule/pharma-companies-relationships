<?php

namespace App\Http\Controllers;

use App\Models\Configuration;
use Illuminate\Http\Request;

class ConfigurationsController extends Controller
{


    function index()
    {
        return response()->json(Configuration::orderBy('updated_at', 'desc')->get());
    }


    function store()
    {
        request()->validate([
            'key' => 'required|max:255',
            'value' => 'max:1024',
        ]);

        $params = request()->only(['key', 'value']);

        $config = Configuration::create($params);

        return response()->json($config);
    }


    function update(Configuration $configuration)
    {
        request()->validate([
            'key' => 'required|max:255',
            'value' => 'max:1024',
        ]);

        $params = request()->only(['key', 'value']);

        $configuration->update($params);

        return response()->json($configuration);
    }

}
