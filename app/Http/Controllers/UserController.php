<?php

namespace App\Http\Controllers;

use App\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function profile()
    {
        try {
            $user = User::where('email', Auth::user()->email)->first();

            $message = "User data fetched successfully";
            $status = http_response_code();
            return response()->json(compact('status', 'message', 'user'), 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e], 200);
        }
    }
}
