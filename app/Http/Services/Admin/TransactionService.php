<?php

namespace App\Http\Services\Admin;

use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionService{

    public function getAllDeposit()
    {
        $tarnsactions = Transaction::where('transaction_type', 'deposit')->with('user')->latest()->get();

        return $tarnsactions;
    }

     public function getAllWithdrawl()
    {
        $tarnsactions = Transaction::where('transaction_type', 'withdrawal')->with('user')->latest()->get();

        return $tarnsactions;
    }

    public function deposit(Request $request)
    {
        DB::beginTransaction();

        try {

            //storing deposit

            $tarnsaction = new Transaction();

            $tarnsaction->user_id = $request->user_id;
            $tarnsaction->transaction_type = $request->transaction_type;
            $tarnsaction->amount = $request->amount;
            $tarnsaction->fee = $request->fee;
            $tarnsaction->date = $request->date;

            $tarnsaction->save();

            $user = User::findOrFail($request->user_id);

            $total = $user->balance + $request->amount;

            User::where('id', $request->user_id)->update(['balance' => $total]);

            DB::commit();

            return $tarnsaction;

        } catch (\Throwable $th) {

            DB::rollBack();

            throw $th;
        }
    }

    public function withdraw(Request $request)
    {
        DB::beginTransaction();

        try {

            $user = User::findOrfail($request->user_id);

            if($user->account_type == 'individual')
            {
                $fee = '';

                $isFriday = (date('N', strtotime($request->date)) == 5);

                $fee = $isFriday ? 0 : $request->fee;

                 // Define the threshold for the first free withdrawal
                if($freeWithdrawalThreshold = 1000)
                {
                    // Calculate the total amount of withdrawals made by the user across all transactions
                    $totalWithdrawals = Transaction::where('user_id', $user->id)
                        ->where('transaction_type', 'withdrawal')
                        ->sum('amount');

                    // Check if the total withdrawals exceed the free withdrawal threshold
                    if ($totalWithdrawals <= $freeWithdrawalThreshold) {
                        // No fee charged for the first $1000 withdrawal
                        $fee = 0;
                    } else {
                        $fee = $request->fee;
                    }
                }


                // Define the threshold for the first free withdrawal each month
                if ($freeWithdrawalThreshold = 5000)
                {
                    // Get the current month and year
                    $currentMonth = Carbon::now()->format('m');
                    $currentYear = Carbon::now()->format('Y');

                    // Calculate the total amount of withdrawals made by the user within the current month
                    $totalWithdrawals = Transaction::where('user_id', $user->id)
                        ->where('transaction_type', 'withdrawal')
                        ->whereYear('date', $currentYear)
                        ->whereMonth('date', $currentMonth)
                        ->sum('amount');

                    // Check if the total withdrawals exceed the free withdrawal threshold for the current month
                    if ($totalWithdrawals <= $freeWithdrawalThreshold) {
                        $fee = 0;
                    } else {
                        // No fee charged for the first $5000 withdrawal each month
                        $fee = $request->fee;
                    }
                }



                //withdrawl storing
                $tarnsaction = new Transaction();

                $tarnsaction->user_id = $request->user_id;
                $tarnsaction->transaction_type = $request->transaction_type;
                $tarnsaction->amount = $request->amount;
                $tarnsaction->fee = $fee;
                $tarnsaction->date = $request->date;

                $tarnsaction->save();

                // Usage example
                $user = User::find($user->id);

                // Withdraw money
                $withdrawalResult = $this->withdrawMoneyIndividualAccount($user, $request->amount, $fee);
                DB::commit();
                return $withdrawalResult;
            }

            if($user->account_type == 'business')
            {
                $fee = '';

                if($businessAccountThreshold = 50000)
                {
                    // Define the fee percentage for Business accounts
                    $businessAccountFeePercentage = 0.015;

                    // Get the current month and year
                    $currentMonth = Carbon::now()->format('m');
                    $currentYear = Carbon::now()->format('Y');

                    // Calculate the total amount of withdrawals made by the user within the current month
                    $totalWithdrawals = Transaction::where('user_id', $user->id)
                        ->where('transaction_type', 'withdrawal')
                        ->whereYear('date', $currentYear)
                        ->whereMonth('date', $currentMonth)
                        ->sum('amount');

                    // Check if the user has a Business account and if the total withdrawals exceed the threshold
                    if ($totalWithdrawals <= $businessAccountThreshold) {
                        // Set the fee percentage to the reduced rate for Business accounts
                        $fee = $businessAccountFeePercentage;
                    } else {
                        // Set the default fee percentage
                        $fee = $request->fee; // Example default fee percentage
                    }
                }

                 //withdrawl storing
                $tarnsaction = new Transaction();

                $tarnsaction->user_id = $request->user_id;
                $tarnsaction->transaction_type = $request->transaction_type;
                $tarnsaction->amount = $request->amount;
                $tarnsaction->fee = $fee;
                $tarnsaction->date = $request->date;

                $tarnsaction->save();

                // Usage example
                $user = User::find($user->id);

                // Withdraw money
                $withdrawalResult = $this->withdrawMoneyBusinessAccount($user, $request->amount, $request->fee);

                DB::commit();

                return $withdrawalResult;
            }

            DB::rollBack(); // Rollback the transaction
            return response()->json(['success' => false, 'message' => 'Invalid account type.'], 400);

        } catch (\Throwable $th) {

            DB::rollBack();

            throw $th;
        }
    }

    public function withdrawMoneyIndividualAccount($user, $amount, $feePercentage)
    {

        $fee = ($amount * $feePercentage) / 100;

        $totalWithdrawalAmount = $amount + $fee;

        if ($totalWithdrawalAmount <= $user->balance) {
            $user->balance -= $totalWithdrawalAmount;
            $user->save();

            $data = [
                'bank_statement' => 'Withdrawal successful. Fee: ' . $fee . ', Total Withdrawal Amount: ' . $totalWithdrawalAmount,
                'remaining_balance' => $user->balance,
            ];

            return $data;
        } else {
            $data =  [
                'bank_statement' => 'Insufficient balance.',
                'remaining_balance' => $user->balance,
            ];

            return $data;
        }
    }

    public function withdrawMoneyBusinessAccount($user, $amount, $feePercentage)
    {
        $fee = ($amount * $feePercentage) / 100;

        $totalWithdrawalAmount = $amount + $fee;

        if ($totalWithdrawalAmount <= $user->balance) {
            $user->balance -= $totalWithdrawalAmount;
            $user->save();

            $data = [
                'bank_statement' => 'Withdrawal successful. Fee: ' . $fee . ', Total Withdrawal Amount: ' . $totalWithdrawalAmount,
                'remaining_balance' => $user->balance,
            ];

            return $data;
        } else {
            $data =  [
                'bank_statement' => 'Insufficient balance.',
                'remaining_balance' => $user->balance,
            ];

            return $data;
        }
    }
}
