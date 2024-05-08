<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Helper\GlobalResponse;
use App\Http\Controllers\Controller;
use App\Http\Services\Admin\TransactionService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class TransactionController extends Controller
{
    private $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    public function getAllDeposit()
    {
        try {
            $transaction = $this->transactionService->getAllDeposit();

            return GlobalResponse::success($transaction, "All deposit transaction fetch successful", Response::HTTP_OK);

        } catch (Exception $e) {

             return GlobalResponse::error("", $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

     public function getAllWithdrawl()
    {
        try {
            $transaction = $this->transactionService->getAllWithdrawl();

            return GlobalResponse::success($transaction, "All withdrawl transaction fetch successful", Response::HTTP_OK);

        } catch (Exception $e) {

             return GlobalResponse::error("", $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function deposit(Request $request)
    {
        try {

            $transaction = $this->transactionService->deposit($request);

            return GlobalResponse::success($transaction, "Deposit successful", Response::HTTP_CREATED);

        } catch (ValidationException $ex) {

            return GlobalResponse::error($ex->errors(), $ex->getMessage(), $ex->status);

        }catch(Exception $ex){

            return GlobalResponse::error("", $ex->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function withdraw(Request $request)
    {
        try {

            $transaction = $this->transactionService->withdraw($request);

            return GlobalResponse::success($transaction, "withdraw successful", Response::HTTP_CREATED);

        } catch (ValidationException $ex) {

            return GlobalResponse::error($ex->errors(), $ex->getMessage(), $ex->status);

        }catch(Exception $ex){

            return GlobalResponse::error("", $ex->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
