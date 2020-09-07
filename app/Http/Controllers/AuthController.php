<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Mail\forgotpassword;
use App\User;
use Error;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tymon\JWTAuth\JWTAuth;

class AuthController extends Controller
{
    /**
     * @var \Tymon\JWTAuth\JWTAuth
     */
    protected $jwt;

    public function __construct(JWTAuth $jwt)
    {
        $this->jwt = $jwt;
    }

    public function login(Request $request)
    {
        $this->validate($request, [
            'email'    => 'required|email|max:255',
            'password' => 'required',
        ]);

        try {
            if (!$token = $this->jwt->attempt($request->only('email', 'password'))) {
                return response()->json(['user_not_found'], 404);
            }
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['token_expired'], 500);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['token_invalid'], 500);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['token_absent' => $e->getMessage()], 500);
        }

        $email = $request->input('email');

        return response()->json(compact('email', 'token'));
    }

    public function register(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:255',
            'email'    => 'required|email|max:255|unique:users',
            'password' => 'required|min:4',
            'phone' => 'required|numeric|min:11|unique:users',
            'department' => 'required',
            'institution' => 'required'
        ]);

        $user =  new User();
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->password = Hash::make($request->input('password'));
        $user->phone = $request->input('phone');
        $user->department = $request->input('department');
        $user->institution = $request->input('institution');
        $user->save();

        $message = "Registration successful. Proceed to login";
        $data = $user;

        http_response_code(201);
        $status = http_response_code();
        return response()->json(compact('status', 'message', 'data'));
    }

    public function forgotpassword(Request $request)
    {

        $code = $this->generateresetcode();
        $user = User::where('email', $request->input('email'))->first();
        try {
            if ($user) {
                $user->resetcode = $code;
                Mail::to('sixpathdev@gmail.com')->send(new forgotpassword($user->name, $code));

                http_response_code(200);
                $status = http_response_code();
                $message = "An email containing your reset code has been sent to " . $user->email;
                $success = true;
                return response()->json(compact('status', 'success', 'message'));
            }
        } catch (Exception $e) {
            throw new Error($e);
        }
    }

    public function resetpassword(Request $request)
    {
        $this->validate($request, [
            'resetcode' => 'required|max:9',
            'password'    => 'required',
            'email' => 'required|unique:users|email'
        ]);
        $userWithCode = User::where('resetcode', $request->input('resetcode'))->first();
        if ($userWithCode) {
            $userWithCode->password = Hash::make($request->input('password'));
            $userWithCode->save();

            http_response_code(200);
            $status = http_response_code();
            $message = "Your password has been changed successfully";
            $success = true;
            return response()->json(compact('status', 'success', 'message'));
        } else {
            http_response_code(400);
            $status = http_response_code();
            $message = "User not found";
            $success = false;
            return response()->json(compact('status', 'success', 'message'));
        }
    }

    protected function generateresetcode()
    {
        $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyzAZENDIKIDHYGTGYHJK';
        $code = substr(str_shuffle($permitted_chars), 0, 8);
        return $code;
    }

    public function template()
    {
        return view('emails.forgotpassword');
    }
}
