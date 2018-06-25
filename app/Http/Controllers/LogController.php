<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserActivity;
use App\Models\UserActivityType;

class LogController extends Controller
{
    public function logData (Request $request)
    {
        $componentName = request('componentName');
        
        $componentAction = request('componentAction');
        
        $userActivityType = UserActivityType::firstOrCreate([
            'component_name' => $componentName,
            'component_action' => $componentAction
        ], [
            'component_name' => $componentName,
            'component_action' => $componentAction
        ]);

        // $userActivityType = new UserActivityType();
        // $userActivityType->component_name = $componentName;
        // $userActivityType->component_action = $componentAction;
        // $userActivityType->save();
        
        // $userActivity = new UserActivity();

        // $userActivity->user_id = 3;

        // $userActivity->activity_type_id = $userActivityType->id;

        // $userActivity->user_agent = '123123123';

        // $userActivity->payload = '123123123';

        // $userActivity->save();

        return response()->json([
            'success' => true
        ]);
    }
}
