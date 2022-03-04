<?php

namespace App\Http\Controllers;

use App\Traits\AuthHelpers\AuthUserManager;
use App\Traits\RequestHelpers\APIResponse;
use App\Traits\RequestHelpers\RemoteAPIServerSportBookActions;
use Illuminate\Http\Request;

class SportBookController extends Controller
{
    use APIResponse;
    use AuthUserManager;
    use RemoteAPIServerSportBookActions;
    public $user;

    public function __construct(Request $request)
    {
        $this->user = $this->getCurrentUser($request);
    }

    public function fetchSports (){
        $response = $this->initiateFetchAllSports();
        return $this->successResponse($response, 200);
    }
}
