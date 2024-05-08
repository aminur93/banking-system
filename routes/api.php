<?php

use App\Http\Controllers\Api\V1\Admin\TransactionController;
use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\RegisterController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/* Auth route start*/
Route::group(['prefix' => 'v1/auth'], function (){

    /*register route start*/
    Route::post('/register', [RegisterController::class, 'register'])->name('register');
    /*register route end*/

    /*login route start*/
    Route::post('/login', [LoginController::class, 'login']);
    /*login route end*/

    Route::group(['middleware' => 'jwtAuth'], function (){

        /*logout route start*/
        Route::post('/logout', [LoginController::class, 'logout']);
        /*logout route end*/

        /*check token route start*/
        Route::post('checkToken', [LoginController::class, 'checkToken']);
        /*check token route end*/
    });
});
/* Auth route end*/

/*Admin route start*/
Route::group(['prefix' => 'v1/admin', 'middleware' => 'jwtAuth'], function (){

    /*transaction route start*/
    Route::get('/transaction/getAllDeposit', [TransactionController::class, 'getAllDeposit']);
    Route::get('/transaction/getAllWithdrawal', [TransactionController::class, 'getAllWithdrawl']);
    Route::post('/transaction/deposit', [TransactionController::class, 'deposit'])->name('transaction.deposit');
    Route::post('/transaction/withdrawal', [TransactionController::class, 'withdraw'])->name('transaction.withdraw');
    /*transaction route end*/
});
/*Admin route end*/
