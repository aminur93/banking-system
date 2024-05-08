<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Helper\GlobalResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Services\Auth\RegisterService;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class RegisterController extends Controller
{
     private $registerService;

    public function __construct(RegisterService $registerService)
    {
        $this->registerService = $registerService;
    }

    public function register(RegisterRequest $request)
    {
        try {

            $user = $this->registerService->register($request);

            return GlobalResponse::success($user, "Register successful", Response::HTTP_CREATED);

        }catch (ValidationException $exception){

            return GlobalResponse::error($exception->errors(), $exception->getMessage(), $exception->status);

        }catch (\Exception $exception){

            return GlobalResponse::error("", $exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
