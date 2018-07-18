<?php

namespace App\Http\Controllers;

use App\Models\WebsiteNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebsiteNotificationsController extends Controller
{

    public $validatorRules = [
        'key' => 'required|max:255',
        'title' => 'max:1024',
        'body' => 'max:1024',
        'expired_at' => 'max:1024',
    ];

    function index()
    {
        return response()->json(WebsiteNotification::orderBy('expired_at', 'desc')->get());
    }


    function store()
    {
        request()->validate($this->validatorRules);

        $params = request()->only(['key', 'title', 'body', 'expired_at']);

        $config = WebsiteNotification::create($params);

        return response()->json($config);
    }


    function update(WebsiteNotification $websiteNotification)
    {
        request()->validate($this->validatorRules);

        $params = request()->only(['key', 'title', 'body', 'expired_at']);

        $websiteNotification->update($params);

        return response()->json($websiteNotification);
    }

}
