<?php

namespace App\Http\Controllers;

use App\Models\WebsiteNotification;
use Illuminate\Http\Request;

class WebsiteNotificationsController extends Controller
{


    function index()
    {

        $activeNotifications = WebsiteNotification::activeNotifications()->get();

        return response()->json([
            'active_notifications' => $activeNotifications
        ]);
    }

}
