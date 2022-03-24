<?php

namespace App\Http\Controllers;

use App\Models\PadiWinUser;
use App\Models\Token;
use App\Models\User;
use App\Traits\AuthHelpers\AuthUserManager;
use App\Traits\RequestHelpers\APIResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PadiWinController extends Controller
{
    use APIResponse;
    use AuthUserManager;
    public $user;
    public $token;

    public function __construct(Request $request)
    {
        $this->user  = $this->getCurrentUser($request);
        if ($this->user){
            $this->token = Token::where('username', $this->user->username)->first()->token;
        }
    }

    public function createPadiWinUserLink(){
        //Check if the user has a padiWinLink Already
        $padiLink = PadiWinUser::where('user_id', $this->user->id)->first();
        if ($padiLink){
            return $this->errorResponse([
                'errorCode' => 'PADIWIN_ACCOUNT_ERROR',
                'message'   => 'You already have a padiwin account'
            ], 409);
        }

        $ref_code = $this->user->username.'_'.Str::random(40);
        PadiWinUser::create([
            'user_id'  => $this->user->id,
            'user_ref_id' => $ref_code,
        ]);

        return $this->successResponse([
            'errorCode'     => 'SUCCESS',
            'message'       => 'Padi win account created',
            'referrer_link' => getenv('APP_URL').'/padiwin/sign-up/'.$ref_code
        ], 201);



    }

    public function generateMyLink(){
        if (!$this->user->padiWin){
            return $this->errorResponse([
                'errorCode' => 'PADIWIN_ACCOUNT_ERROR',
                'message'   => 'You don\'t have a padiwin account'
            ], 404);
        }

        $ref_code = $this->user->padiWin->user_ref_id;
        return $this->successResponse([
            'errorCode'     => 'SUCCESS',
            'referrer_link' => getenv('APP_URL').'/padiwin/sign-up/'.$ref_code
        ], 201);
    }




}
