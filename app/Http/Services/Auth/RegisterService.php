<?php

namespace App\Http\Services\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class Register{

    public function register(Request $request)
    {
        DB::beginTransaction();

        try{

            //storing new user

            $user = new User();

            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = $request->password;
            $user->account_type = $request->account_type;
            $user->balance = $request->balance;

            $user->save();

            DB::commit();

            return $user;

        }catch(Throwable $th){
            DB::rollBack();

            throw $th;
        }
    }
}
