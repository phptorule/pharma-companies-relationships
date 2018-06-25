<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserActivity;
use App\Models\UserActivityType;
use Tymon\JWTAuth\Facades\JWTAuth;

class LogController extends Controller
{
    public function logData (Request $request)
    {
        $componentName = request('componentName');
        
        $componentAction = request('componentAction');

        $payload = request('payload');
        
        $userActivityType = UserActivityType::where('component_name', $componentName)
            ->where('component_action', $componentAction)
            ->first();

        if (empty($userActivityType)) {
            $userActivityType = new UserActivityType();

            $userActivityType->component_name = $componentName;

            $userActivityType->component_action = $componentAction;

            $userActivityType->save();
        }

        $user = JWTAuth::user();
        
        $userActivity = new UserActivity();

        $userActivity->user_id = $user->id;

        $userActivity->url = url()->previous();

        $userActivity->activity_type_id = $userActivityType->id;

        $userActivity->user_agent = request()->header('User-Agent');

        $userActivity->ip = request()->ip();

        $userActivity->payload = ! empty($payload) ? $payload : '';

        $userActivity->save();

        return response()->json([
            'success' => true
        ]);
    }
}
