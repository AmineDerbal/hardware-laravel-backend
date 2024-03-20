<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        // return all the usersData except passwords
        return User::select('id', 'name', 'email', 'role', 'image_url', 'email_verified_at')->get();
    }
}
