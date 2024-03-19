<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Models\EmailConfirmation;


class RegisterController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required',
            'role' => 'sometimes|string',
            'accessToken' => 'sometimes|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }
        $isAdminUser = false;

        // create user
        $user = new User;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->save();

        // if access token check if user is admin
        if (isset($request->accessToken)) {
            $requestingAdminUser = User::where('access_token', $request->accessToken)->first();
            if ($requestingAdminUser->role == 'admin') {
                $isAdminUser = true;
            }
        }

        // if role is set
        if (isset($request->role) && $isAdminUser) {
            $roleChoices = ['admin', 'user'];
            if (!in_array($request->role, $roleChoices)) {
                return response()->json([
                    'errors' => [
                        'role' => ['Invalid role']
                    ]
                ], 422);
            }
            $user->role = $request->role;
            $user->save();
        }

        // if the user is an admin, we do not create or send an email verification the user is immediately activated
        if ($isAdminUser) {
            $user->email_verified_at = now();
            $user->save();
            return response()->json([
                'success' => true,
                'message' => 'User created and activated successfully',
            ]);
        }

        $token = Str::random(64);

        // if token already exists
        while (EmailConfirmation::where('token', $token)->exists()) {
            $token = Str::random(64);
        }

        $emailConfirmation = new EmailConfirmation;
        $emailConfirmation->token = $token;
        $emailConfirmation->user_id = $user->id;
        $emailConfirmation->save();

        Mail::send('emails.user_verification_email', ['token' => $token], function ($message) use ($user) {
            $message->to($user->email);
            $message->subject('Email Verification Mail');
        });

        return response()->json([
            'success' => true,
            'message' => 'User created successfully, please check your email for confirmation',
        ]);
    }

    public function verifyEmail($token)
    {
        $emailConfirmation = EmailConfirmation::where('token', $token)->first();

        // if token not found
        if (!$emailConfirmation) {
            return response()->json([
                'errors' => [
                    'token' => ['Invalid token']
                ]
            ], 422);
        }
        $user = $emailConfirmation->user;

        // if email already verified
        if ($user->email_verified_at) {
            $emailConfirmation->delete();
            return response()->json([
                'errors' => [
                    'token' => ['Email already verified']
                ]
            ], 422);
        }

        // if email not verified
        $user->email_verified_at = now();
        $user->save();
        $emailConfirmation->delete();
        return response()->json([
            'success' => true,
            'message' => 'Email verified successfully'
        ]);
    }
}
