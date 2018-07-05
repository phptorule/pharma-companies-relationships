<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function createUser () {
        $name = request('name');

        $email = request('email');

        $role = request('role');

        $password = request('password');

        $confirmPassword = request('confirmPassword');

        $link = request('link');

        $user = new User();

        $user->name = $name;
        
        $user->name = $email;

        $user->role = $role;

        $user->password = bcrypt($password);

        $user->link = $link;

        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'ok'
        ]);
    }
}
