<?php

namespace App\Http\Controllers;

use App\Services\GlobalHelper;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    public function createUser (Request $request) {
        $request->validate([
            'name' => 'required',
            'email' => 'required|unique:rl_users',
            'role' => 'required',
            'password' => 'confirmed|required',
            'password_confirmation' => 'required'
        ]);

        $name = request('name');

        $email = request('email');

        $role = request('role');

        $password = request('password');

        $confirmPassword = request('password_confirmation');

        $link = request('link');

        $user = new User();

        $user->name = $name;
        
        $user->email = $email;

        $user->role = $role;

        $user->password = bcrypt($password);

        $user->link = $link;

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'User successfully created'
        ]);
    }


    public function editUser (Request $request) {
        $arr = [
            'name' => 'required',
            'role' => 'required'
        ];
        
        if (request('changePassword')) {
            $arr['password'] = 'confirmed|required';
            $arr['password_confirmation'] = 'required';
        }

        $request->validate($arr);

        $userId = request('userId');

        $name = request('name');

        $email = request('email');

        $role = request('role');


        if(request('password') && request('password') === request('password_confirmation')) {
            $password = request('password');
        }


        $link = request('link');

        $user = User::whereEmail($email)->where('id', '<>', $userId)->first();

        if ($user) {
            return response()->json([
                'success' => false,
                'message' => 'This email already exists'
            ]);
        }

        $user = User::whereId($userId)->first();

        if ($user) {
            $user->name = $name;
            
            $user->email = $email;
    
            $user->role = $role;

            if(isset($password)) {
                $user->password = bcrypt($password);
            }
    
            $user->link = $link;
    
            $user->save();
        } else {
            return response()->json([
                'success' => false,
                'message' => 'User does not exists'
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'User successfully edited'
        ]);
    }


    public function getUsersPaginated (Request $request)
    {
        $users = User::paginate(10);

        return response()->json($users);
    }


    function getUsersActivities()
    {
        $interval = request()->get('interval') ?? 30;
        $userNumber = request()->get('user_number') ?? 5;

        $mostActiveUserIds = $this->getMostActiveUsers($interval, $userNumber);

        $rawChartData = $this->queryChartDataForUserActivity($interval, $mostActiveUserIds);

        $topUserNames = array_values(array_unique($rawChartData->pluck('name')->toArray()));

        $preData = [];

        foreach ($rawChartData as $i => $result) {
            $preData[$result->date] = array_fill(0, count($topUserNames) + 1, 0);
        }

        foreach ($rawChartData as $i => $result) {

            $preData[$result->date][0] = $result->date;

            if(($index = array_search($result->name, $topUserNames)) !== false) {

                $indexPlus1 = $index + 1;

                $preData[$result->date][$indexPlus1] = $result->activity;
            }

        }

        $titles = array_merge(['Date'],$topUserNames);

        array_unshift($preData, $titles);

        $graphData =  array_values($preData);

        return response()->json($graphData);
    }


    function getMostActiveUsers($interval, $limit = 5)
    {
        $selectSql = "
            u.id,  
            u.name,
            COUNT(ua.id) as activity
        ";

        $users = DB::table('rl_user_activity AS ua')
            ->selectRaw($selectSql)
            ->join('rl_users as u', 'ua.user_id', '=', 'u.id')
            ->whereRaw('ua.created_at BETWEEN (NOW() - INTERVAL ? DAY) AND NOW()', [$interval])
            ->groupBy(['u.id'])
            ->orderBy('activity', 'desc')
            ->limit($limit);

        return $users->get()->pluck('id');
    }


    function queryChartDataForUserActivity ($interval, $mostActiveUserIds)
    {
        $selectSql = "
            DATE_FORMAT(ua.created_at, '%d-%m-%Y') as date,
            COUNT(ua.id) as activity,
            u.id,  
            u.name
        ";

        $activities = DB::table('rl_user_activity AS ua')
            ->selectRaw($selectSql)
            ->join('rl_users as u', 'ua.user_id', '=', 'u.id')
            ->whereRaw('ua.created_at BETWEEN (NOW() - INTERVAL ? DAY) AND NOW()', [$interval])
            ->whereIn('ua.user_id', $mostActiveUserIds)
            ->groupBy(['date', 'u.id'])
            ->orderBy('ua.created_at');

        $activities = $activities->get();

        return $activities;
    }
}
