<?php

namespace App\Http\Services\Auth;

use App\Helper\GlobalResponse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class Login{

    public function login(Request $request)
    {
         $loginField = request()->input('email');

        $credentials = null;

        $loginType = filter_var($loginField, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        $user = User::where('email', '=', $loginField)->first();

        if ($user == null)
        {
            return GlobalResponse::error("", "Email does not exists on record", Response::HTTP_BAD_REQUEST);
        }else{

            if (Hash::check($request->password, $user->password))
            {
                request()->merge([$loginType => $loginField]);

                $credentials = request([$loginType, 'password']);

                if (!$token = JWTAuth::attempt($credentials))
                {

                    return GlobalResponse::error("", "Unauthorized", Response::HTTP_UNAUTHORIZED);

                }else{

                    return $this->responseWithToken($token);
                }
            }else{
                return GlobalResponse::error("", "Password did not match", Response::HTTP_BAD_REQUEST);
            }
        }
    }

    public function logout(Request $request)
    {
        $token = $request->header("Authorization");

        try {

            JWTAuth::invalidate(JWTAuth::getToken());

        } catch (JWTException $e) {

            return $e;
        }
    }

    public function responseWithToken($token)
    {

        $data = [
            "user" => Auth::user(),
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $this->guard()->factory()->getTTL() * 60
        ];
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\Guard
     */
    public function guard()
    {
        return Auth::guard('api');
    }
}
