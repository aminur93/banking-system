<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class JwtAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
         try {

            $user = JWTAuth::parseToken()->refresh();

        }catch (\Exception $exception){

            if ($exception instanceof TokenExpiredException){

                $newToken = JWTAuth::parseToken()->refresh();

                return response()->json(['success' => false, 'token' => $newToken, 'status' => 'expired'], 200);

            }else if($exception instanceof TokenInvalidException){

                return response()->json(['success' => false, 'message' => 'token Invalid'], 401);

            }else{

                return response()->json(['success' => false, 'message' => 'token not found'], 401);
            }
        }

        return $next($request);
    }
}
