<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Mockery\Exception;
use Tymon\JWTAuth\Facades\JWTAuth;
use Image;
use File;
use Storage;
use App\Models\User;
use Hash;

class UserController extends Controller
{
    public $defaultAvatarName = 'profile-pictures/default-avatar.png';

    function showLoggedUserData ()
    {
        $user = JWTAuth::user();
        $originalAvatar = $user->getOriginal('avatar');
        return response()->json([
            'success' => true, 
            'data'=>JWTAuth::user(), 
            'originalAvatar' => $originalAvatar
        ]);
    }


    function updateUserProfile ()
    {
        $data = request()->all();

        $user = JWTAuth::user();

        try {
            $user->update($data);
            return response()->json(['success' => true, 'data'=>JWTAuth::user()]);
        }
        catch (Exception $e) {
            return response()->json(['success' => false, 'message'=>$e->getMessage()], 501);
        }
    }


    function updateUserProfilePicture(Request $request)
    {
        $user = JWTAuth::user();

        $this->validate($request,[
            'file'=> 'mimes:png,jpeg,jpg|max:1024'
        ]);

        if(!$request->hasFile('profile-picture'))
        {
            return response()->json(['success' => false, 'message' => 'File not provided'], 404);
        }

        try {
            $user->updateProfilePicture($request->file('profile-picture'));
        }
        catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'File not provided'], 404);
        }

        return response()->json(['success' => true, 'data' => $user]);

    }

    public function updateProfilePicture (Request $request)
    {
        $user = JWTAuth::user();

        $image = $request->file('profile-picture');

        $oldAvatar = $user->getOriginal('avatar');

        if ($image) {
            $extension = $image->getClientOriginalExtension();

            $originalName = $image->getClientOriginalName();

            if ($extension == 'jpg' || $extension == 'jpeg' || $extension == 'png') {
                $imageName = now()->format('Y-m-d-H-i-s') . '-' . $originalName;

                $img = Image::make($image->getRealPath());

                $img->save(storage_path("app/public/profile-pictures/$imageName"));

                $user->avatar = "profile-pictures/$imageName";

                $user->save();

                if (File::exists(storage_path('app/public/' . $oldAvatar)) && $oldAvatar !== $this->defaultAvatarName) {
                    File::delete(storage_path('app/public/' . $oldAvatar));
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => "Only jpg/jpeg/png files are allowed!"
                ]);
            }
        }

        $originalAvatar = $user->getOriginal('avatar');

        return response()->json([
            'success' => true,
            'data' => $user,
            'originalAvatar' => $originalAvatar
        ]);
    }

    public function removeAvatar (Request $request)
    {
        $user = JWTAuth::user();

        $oldAvatar = $user->getOriginal('avatar');

        if (File::exists(storage_path('app/public/' . $oldAvatar))) {
            try {
                File::delete(storage_path('app/public/' . $oldAvatar));
            }
            catch (Exception $e) {
                return response()->json(['success' => false, 'message' => 'File not provided'], 404);
            }
        }

        $user->avatar = $this->defaultAvatarName;

        $user->save();

        $originalAvatar = $user->getOriginal('avatar');

        return response()->json([
            'success' => true,
            'data' => $user,
            'originalAvatar' => $originalAvatar
        ]);
    }

    public function updateProfileSettings (Request $request)
    {
        $name = $request->name;

        $email = $request->email;

        $user = JWTAuth::user();

        if (isset($name, $email) && $name != '' && $email != '') {
            $user->name = $name;

            $user->email = $email;

            $user->save();
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Name and email cannot be empty!'
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    public function changePassword (Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'confirmed|required|min:6',
            'new_password_confirmation' => 'required'
        ]);

        $user = JWTAuth::user();
        
        $currentPassword = $request->current_password;

        if (Hash::check($currentPassword, $user->password)) {
            $newPassword = $request->new_password;

            $hashedPas = Hash::make($request->new_password);
            
            $user->password = $hashedPas;

            $user->save();

            return response()->json([
                'success' => true
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Wrong current password'
            ]);
        }
    }

    public function getUsersPaginated (Request $request)
    {
        $users = User::paginate(10);

        return response()->json($users);
    }

}
