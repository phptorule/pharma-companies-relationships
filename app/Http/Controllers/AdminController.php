<?php

namespace App\Http\Controllers;

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

        $password = request('password');

        $confirmPassword = request('password_confirmation');

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
    
            $user->password = bcrypt($password);
    
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
        $selectSql = "
            DATE_FORMAT(ua.created_at, '%d-%m-%Y') as date,
            COUNT(ua.id) as activity,
            u.id,  
            u.name
        ";

        $activities = DB::table('rl_user_activity AS ua')
                        ->selectRaw($selectSql)
                        ->join('rl_user_activity_type AS uat', 'ua.activity_type_id', '=', 'uat.id')
                        ->join('rl_users as u', 'ua.user_id', '=', 'u.id')
                        ->groupBy(['date', 'u.id'])
                        ->orderBy('date');

        return response()->json($activities->get());
    }
}
