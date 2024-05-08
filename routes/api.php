<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/* Auth route start*/
Route::group(['prefix' => 'v1/auth'], function (){

    /*register route start*/
    Route::post('/register', [\App\Http\Controllers\Api\V1\Auth\RegisterController::class, 'register'])->name('register');
    /*register route end*/

    /*login route start*/
    Route::post('/login', [\App\Http\Controllers\Api\V1\Auth\LoginController::class, 'login']);
    /*login route end*/

    Route::group(['middleware' => 'jwtAuth'], function (){

        /*logout route start*/
        Route::post('/logout', [\App\Http\Controllers\Api\V1\Auth\LoginController::class, 'logout']);
        /*logout route end*/

        /*check token route start*/
        Route::post('checkToken', [\App\Http\Controllers\Api\V1\Auth\LoginController::class, 'checkToken']);
        /*check token route end*/
    });
});
/* Auth route end*/
