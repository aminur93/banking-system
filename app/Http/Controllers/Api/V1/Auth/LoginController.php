<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Helper\GlobalResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Services\Auth\LoginService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    private $loginService;

    public function __construct(LoginService $loginService)
    {
        $this->loginService = $loginService;
    }

     public function login(LoginRequest $request)
    {
        try {

            $login = $this->loginService->login($request);

            return GlobalResponse::success($login, "Login successful", Response::HTTP_OK);

        }catch (ValidationException $exception){

            return GlobalResponse::error($exception->errors(), $exception->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);

        }catch (\Exception $exception){

            return GlobalResponse::error("", $exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function logout(Request $request)
    {
        $this->loginService->logout($request);

        return GlobalResponse::success("", "Logout successful", Response::HTTP_OK);
    }

    public function checkToken()
    {
        return response()->json(['success' => true], Response::HTTP_OK);
    }
}
