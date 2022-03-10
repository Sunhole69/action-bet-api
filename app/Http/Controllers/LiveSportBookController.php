<?php

namespace App\Http\Controllers;


use App\Traits\AuthHelpers\AuthUserManager;
use App\Traits\RequestHelpers\APIResponse;
use App\Traits\RequestHelpers\RemoteAPIServerLiveSportBookActions;
use App\Traits\Utils\ArrayJsonManager;
use Illuminate\Http\Request;

class LiveSportBookController extends Controller
{
    use APIResponse;
    use ArrayJsonManager;
    use AuthUserManager;
    use RemoteAPIServerLiveSportBookActions;
    public $user;

    public function __construct(Request $request)
    {
        $this->user = $this->getCurrentUser($request);
    }

    public function fetchLiveEvents(){

        $response = $this->initiateFetchAllLiveEventsList();

        if ($response['errorCode'] !== "SUCCESS"){
            return $this->errorResponse($response, 422);
        }

        return $this->successResponse($response, 200);

    }

    public function fetchLiveOddsStructure(){

        $response = $this->initiateFetchAllLiveOddStructureList();

        if ($response['errorCode'] !== "SUCCESS"){
            return $this->errorResponse($response, 422);
        }

        return $this->successResponse($response, 200);

    }


}
