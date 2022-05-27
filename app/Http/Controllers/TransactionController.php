<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\PadiWinControl;
use App\Models\PadiWinUser;
use App\Models\PaymentGateway;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WithdrawalRequest;
use App\Traits\AuthHelpers\AuthUserManager;
use App\Traits\JsonBuilders\PaystackJsonRequestBuilder;
use App\Traits\RequestHelpers\APIResponse;
use App\Traits\RequestHelpers\RemoteAPIServerTransactionActions;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    use APIResponse;
    use AuthUserManager;
    use RemoteAPIServerTransactionActions;
    use PaystackJsonRequestBuilder;
    public $user;

    public function __construct(Request $request)
    {
        $this->user = auth()->user();
    }

    public function deposit(Request $request){
        $this->user = auth()->user();
        $request->validate([
            'amount'            => 'required|numeric',
            'payment_type'      => 'Deposit',
            'channel'           => 'required|string',
            'payment_method'    => 'required|string',
            'reference'         => 'nullable|string',
            'transaction_code'  => 'nullable|string',
            'trxref'            => 'nullable|string',
            'note'              => 'required|string|max:255',
        ]);

        $data = [
            'action' => 'deposit_money',
            'amount' => $request->amount,
            'note'   => $request->note,
            'username'   => auth()->user()->username,
            'user_type' => auth()->user()->user_type,
            'payment_type' => 'Deposit',
            'channel'      => $request->channel,
            'payment_method'    => $request->payment_method,
            'reference'     => $request->reference,
            'transaction_code'  => $request->transaction_code,
            'trxref'            => $request->trxref,
            'status'       => 'Success',
        ];

        // Check if the Parent agency has enough credit
//        $agency = User::where('user_type', 'agency')->where('username', $this->user->agency)->first();
//        if ($agency->wallet->balance < $request->amount){
//            return $this->errorResponse([
//                'errorCode' => 'TRANSACTION_ERROR',
//                'message'   => 'Agency does not have enough balance'
//            ], 409);
//        }

//        // Trigger payment gateway here
//        $gatewayResponse = $this->initiatePaymentGateway();
//        if ($gatewayResponse->errorResponse == "FAIL"){
//            return $this->errorResponse($gatewayResponse, 422);
//        }

        // Credit user account
        $creditUserResponse = $this->creditUser($data);
        if ($creditUserResponse['errorCode'] === "SUCCESS"){
            // Credit the referrer 10% of the deposit
            if ($this->user->referred){
                $padiWinControl = PadiWinControl::first();
                $percentage_cut = ($request->amount / 100 )* $padiWinControl->percentage_bonus;

                // Create a transaction for the referrer
                Transaction::create([
                    'user_id'      => $this->user->referrer_id,
                    'payment_type' => 'Padiwin_bonus',
                    'status'       => 'Success',
                    'amount'       => $percentage_cut,

                ]);

                // Update the wallet of the referrer
                $refWallet = Wallet::where('user_id', $this->user->referrer_id)->first();
                $refWallet->padi_win_bonus = $refWallet->padi_win_bonus + $percentage_cut;
                $refWallet->save();
            }
            $user = User::where('email', auth()->user()->email)->with('player')->with('wallet')->with('transactions')->first();

            $token = $user->createToken('myapptoken')->plainTextToken;

            // Generate sanctum auth token for user
            $responseData = [
                'errorCode'    => 'SUCCESS',
                'user'         => $user,
                'token'        => $token,
            ];

            // return the updated user data
            return  $this->successResponseWithCookie($responseData, [
                'name'  => 'token',
                'value' => $token
            ], (5 * 365 * 24 * 60 * 60),200);
        }


        //Finally return the server response if fails
        return $this->errorResponse($creditUserResponse, 400);
    }

    public function withdraw(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric',
            'note'  => 'required|string|max:255',
        ]);
        $data = [
            'amount' => $request->amount,
            'action' => 'withdrawal_money',
            'note'   => $request->note,
            'username'   => $this->user->username,
            'user_type' => $this->user->user_type
        ];

        //If payment is successful through the payment gateway


        // Debit user account
        $debitUserResponse = $this->debitUser($data);
        if ($debitUserResponse['errorCode'] === "SUCCESS"){
            //Finally return the server response if fails
            return $this->successResponse($debitUserResponse, 200);
        }

        //Finally return the server response if fails
        return $this->errorResponse($debitUserResponse, 400);
    }

    public function creditUser($data){
        $data['agency'] = $this->user->username;
        //Initiate remotely, if successful, update locally
        $response = $this->initiateRemoteTransaction($data);

        if ($response['errorCode'] === "SUCCESS"){
            // Create a tramsaction for the user

            Transaction::create([
                'user_id'           => $this->user->id,
                'payment_type'      => 'Deposit',
                'amount'            => $data['amount'],
                'channel'           => $data['channel'],
                'payment_method'    => $data['payment_method'],
                'reference'         => $data['reference'],
                'transaction_code'  => $data['transaction_code'],
                'trxref'            => $data['trxref'],
                'status'            => 'Success',
            ]);

            //Update user wallet
            $this->user->wallet->update([
                'balance'  => auth()->user()->wallet->balance +  $data['amount']
            ]);

            $wallet = Wallet::where('user_id', auth()->user()->id)->first();
            $transactionDetails = [
                "errorCode" => "SUCCESS",
                'transaction-details' => $response['data'],
                'wallet' => $wallet
            ];


            // user_type is Player, then deduct from agency
            if (auth()->user()->user_type === 'player'){
                $agency = User::where('user_type', 'agency')->where('username', $this->user->agency)->first();

                Transaction::create([
                    'user_id'      => $agency->id,
                    'payment_type' => 'Player_credit',
                    'status'       => ucwords($response['errorCode']),
                    'amount'       =>  $data['amount']
                ]);

                $agency->wallet->update([
                    'balance'  => $agency->wallet->balance -  $data['amount']
                ]);

            }

            //Finally return the server response
            return $transactionDetails;
        }
        return $response;
    }

    public function debitUser($data){
        //Initiate remotely, if successful, update locally
        $response = $this->initiateRemoteTransaction($data);

        if ($response['errorCode'] === "SUCCESS"){
           $transaction =  Transaction::create([
                'user_id'      => $this->user->id,
                'payment_type' => 'Withdrawal',
                'status'       => 'pending',
                'amount'       =>  $data['amount']
            ]);

            //Update user wallet
            $this->user->wallet->update([
                'user_id'  => $this->user->id,
                'balance'  => $this->user->wallet->balance -  $data['amount']
            ]);

            $wallet = Wallet::where('user_id', $this->user->id)->first();
            $transactionDetails = [
                "errorCode"           => "SUCCESS",
                'transaction-details' => $response['data'],
                'wallet'              => $wallet
            ];

//            // user_type is Player, then deduct from agency
//            Transaction::create([
//                'user_id' => $this->user->id,
//                'payment_type' => 'Withdrawal',
//                'status'    => ucwords($response['errorCode']),
//                'amount'    =>  $data['amount']
//            ]);

            //Finally return the server response
            return $transactionDetails;
        }
        return $response;
    }

    public function initiatePaymentGateway($data){
        $data = [
            "email" => $this->user,
            "amount" => $data['amount'],
        ];
        $response =  $this->sendPaymentCharge(getenv('PAYSTACK_PUBLIC_KEY'), $data);

        error_log($response['message']);
        return true;
    }

    public function initiatePaymentGatewayTest(Request $request){
        $request->validate([
            'amount' => 'required|numeric',
        ]);
        $data = [
            "email" => $this->user->email,
            "amount" => $request->amount,
        ];
        $response =  $this->sendPaymentCharge(getenv('PAYSTACK_PAYMENT_URL'), $this->buildChargeRequestData($data));

        error_log($response['message']);
        return $this->successResponse($response, 200);
//        return true;
    }

    public function myTransactions(){
        $transactions = Transaction::orderBy('created_at', 'DESC')->where('user_id', $this->user->id)->get();
        return $this->showAll($transactions);
    }

    public function myWallet(){
        $wallet = Wallet::where('user_id', $this->user->id)->first();
        return $this->showOne($wallet);
    }

    public function getPaymentGatewayKeys(Request $request){
//        if(auth()->user()->user_type != 'admin'){
//            return $this->errorResponse([
//                'errorCode'     => 'AUTHORIZATION_ERROR',
//                'message'       => 'You are not authorized to perform this action'
//            ], 401);
//        }

        $request->validate([
            'name'           =>  'required|string|max:255',
        ]);

        $gateway = PaymentGateway::where('name', $request->name)->first();
        if (!$gateway){
            return $this->errorResponse([
                'errorCode' =>  'GATEWAY_ERROR',
                'message'   =>  'Payment gateway not found'
            ], 404);
        }

        return $this->successResponse([
            'errorCode'     =>  'SUCCESS',
            'data'          => $gateway
        ], 200);
    }

    public function updatePaymentGatewayKeys(Request $request){
        if(auth()->user()->user_type != 'admin'){
            return $this->errorResponse([
                'errorCode'     => 'AUTHORIZATION_ERROR',
                'message'       => 'You are not authorized to perform this action'
            ], 401);
        }

        $request->validate([
           'name'           =>  'required|string|max:255',
           'public_key'     =>  'required|string|max:255'
        ]);

        if (strtolower($request->name) === 'monnify' && !$request->contract_code){
            return $this->errorResponse([
                'errorCode'     => 'DATA_ERROR',
                'message'       => 'Please supply contract code'
            ], 422);
        }

        $gateway = PaymentGateway::where('name', $request->name)->first();
        if (!$gateway){
            PaymentGateway::create([
               'name'           =>  $request->name,
               'public_key'     =>  $request->public_key,
               'contract_code'  => $request->contract_code
            ]);
        }else{
            $gateway->public_key    = $request->public_key;
            $gateway->name          = $request->name;
            $gateway->contract_code = $request->contract_code;
            $gateway->save();
        }

        return $this->successResponse([
            'errorCode'     =>  'SUCCESS',
            'message'       => 'Payment gateway updated'
        ], 200);
    }


    public function addBankAccount(Request $request){
        $request->validate([
           'bank'             =>  'required|string',
           'account_type'     =>  'required|string',
           'account_number'   =>  'required|numeric'
        ]);

        $account = BankAccount::where('user_id', auth()->user()->id)->first();
        if (!$account){
            $account = BankAccount::create([
                'user_id'           =>  auth()->user()->id,
                'bank'              => $request->bank,
                'account_type'      => $request->account_type,
                'account_number'    => $request->account_number
            ]);

            return $this->successResponse([
                'errorCode'     =>  'SUCCESS',
                'data'          =>  $account
            ], 201);
        }

        $account->bank            =   $request->bank;
        $account->account_type    =   $request->account_type;
        $account->account_number  =   $request->account_number;
        $account->save();

        return $this->successResponse([
            'errorCode'     =>  'SUCCESS',
            'data'          =>  $account
        ], 200);

    }

    public function fetchMyBankAccount(Request $request){


        $account = BankAccount::where('user_id', auth()->user()->id)->first();
        if (!$account){
            return $this->successResponse([
                'errorCode'     =>  'ACCOUNT_NOT_FOUND',
                'message'          =>  'You don not have a bank account'
            ], 404);
        }

        return $this->successResponse([
            'errorCode'     =>  'SUCCESS',
            'data'          =>  $account
        ], 200);

    }

    public function requestWithdrawal(Request $request){
        $request->validate([
            'amount'    =>  'required|numeric'
        ]);

        // Check user balance
        if (auth()->user()->wallet->balance < $request->amount){
            return $this->errorResponse([
                'errorCode' =>  'WALLET_ERROR',
                'message'   => 'insufficient fund'
            ], 422);
        }

        // Create a withdrawal request for the admin
        WithdrawalRequest::create([
            'user_id'               => auth()->user()->id,
            'amount'                => $request->amount
        ]);

        return $this->successResponse([
           'errorCode'  => 'SUCCESS',
           'message'    => 'Your withdrawal request has been sent'
        ], 201);

    }

}
