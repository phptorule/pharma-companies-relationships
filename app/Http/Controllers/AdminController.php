<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class AdminController extends Controller
{
    public function createUser (Request $request) {
        $request->validate([
            'name' => 'required',
            'email' => 'required|unique:rl_users',
            'role' => 'required',
            'password' => 'confirmed|required|min:6',
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
            'message' => 'ok'
        ]);
    }
}
