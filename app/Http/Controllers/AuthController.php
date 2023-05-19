<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Modules\Users\Services\UserService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


class AuthController extends Controller
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function register(Request $request)
    {
        try {
            return $this->userService->registerUser($request->all());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
         
        return response()->noContent();
    }

    public function login(Request$request){

        $credentials = $request->only('username', 'password');

        if (!$token = Auth::attempt($credentials)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }
        
        try {
            $token = $this->userService->login($request);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
       // return response()->json(['token' => $token], 200);

       return response([
        "status" => "success"
    ], 200)->withCookie(
        'token',
        $token,
        config('jwt.ttl'),
        '/',
        null,
        true,
        true,
        false,
        "None"
    );
    }

}
