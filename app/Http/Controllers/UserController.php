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

    public function updateprofile(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:255',
            'email'    => 'required|email|max:255',
            'phone' => 'required|numeric|min:11',
            'department' => 'required',
            'institution' => 'required'
        ]);

        $user = User::where('email', $request->input('email'))->first();
        $user->name = $request->input('name');
        $user->phone = $request->input('phone');
        $user->department = $request->input('department');
        $user->institution = $request->input('institution');
        $user->save();

        $user->makeHidden(['resetcode']);
 
        $message = "User data updated successfully";
        $status = http_response_code(200);
        return response()->json(compact('status', 'message', 'user'), 200);
    }
}
