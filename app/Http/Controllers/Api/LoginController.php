<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;


class LoginController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        $user = Auth::user();
        if ($user->email_verified_at == null) {
            Auth::logout();
            return response()->json([
                'message' => 'Email not verified'
            ], 401);
        }
        // delete all previous tokens for this user and create a new one

        $token = $user->createToken('auth_token')->accessToken;


        // if there is no user with the name admin and role admin in the database
        if (!User::where('name', 'admin')->where('role', 'admin')->exists()) {
            $newUser = new User;
            $newUser->name = 'admin';
            $newUser->email = 'admin@admin.com';
            $newUser->password = Hash::make('admin');
            $newUser->role = 'admin';
            $newUser->email_verified_at = now();
            $newUser->save();
        }

        $userData = [
            'name' => $user->name,
            'email' => $user->email,
            'image_url' => $user->image_url,
            'role' => $user->role,
            'email_verified_at' => $user->email_verified_at
        ];

        // return user only the user name email image_url and accessToken
        return response()->json([
            'user' => $userData,
            'accessToken' => $token

        ]);
    }
}
