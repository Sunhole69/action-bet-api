<?php

namespace App\Http\Controllers;

use App\Models\SportGroup;
use App\Models\SportLeague;
use App\Models\SportList;
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

    public function fetchPrematchSports (){
        $response = $this->initiateFetchAllPrematchSports();

        if ($response['errorCode'] !== "SUCCESS"){
            return $this->errorResponse($response, 422);
        }

        /*
         * Else if successful
         * Save response in the database if not exist
         */
        $sports  = json_encode($response); // Converts the response array to string
        $sports = json_decode($sports); // Converts the sports string to object
        foreach ($sports->data as $sport){
            // Fetch from sports where the sport name exist
            $sportExist = SportList::where('name', $sport->name)->first();
            if (!$sportExist){
                SportList::create([
                    'sport_id'     => $sport->sport_id,
                    'name'         => $sport->name,
                    'events_count' => $sport->events_count
                ]);
            }
        }
        return $this->successResponse($response, 200);
    }

    public function fetchPrematchSportGroups($sport_id){
       if (!SportList::where('sport_id', $sport_id)->first()){
           return $this->errorResponse([
               'error' => 'Request failed',
               'message' => 'There is no sport with that sport_id'
           ], 422);
       }

        $response = $this->initiateFetchAllPrematchSportGroups($sport_id);

        if ($response['errorCode'] !== "SUCCESS"){
            return $this->errorResponse($response, 422);
        }

        /*
         * Else if successful
         * Save response in the database if not exist
         */
        $groups  = json_encode($response); // Converts the response array to string
        $groups = json_decode($groups); // Converts the sports string to object
        foreach ($groups->data as $group){
            // Fetch from sports where the sport name exist
            $groupExist = SportGroup::where('name', $group->name)->first();
            if (!$groupExist){
                SportGroup::create([
                    'sport_id'     => $sport_id,
                    'group_id'     => $group->group_id,
                    'name'         => $group->name,
                    'country_code' => $group->country_code,
                    'events_count' => $group->events_count
                ]);
            }
        }
        return $this->successResponse($response, 200);

    }

    public function fetchPrematchGroupLeagues($group_id){
        if (!SportGroup::where('group_id', $group_id)->first()){
            return $this->errorResponse([
                'error' => 'Request failed',
                'message' => 'There is no group with that group_id'
            ], 422);
        }

        $response = $this->initiateFetchAllPrematchGroupLeagues($group_id);

        if ($response['errorCode'] !== "SUCCESS"){
            return $this->errorResponse($response, 422);
        }

        /*
         * Else if successful
         * Save response in the database if not exist
         */
        $leagues  = json_encode($response); // Converts the response array to string
        $leagues = json_decode($leagues); // Converts the sports string to object
        foreach ($leagues->data as $league){
            // Fetch from sports where the sport name exist
            $leagueExist = SportLeague::where('name', $league->name)->first();
            if (!$leagueExist){
                SportLeague::create([
                    'group_id'     => $group_id,
                    'champ_id'     => $league->champ_id,
                    'name'         => $league->name,
                    'country_code' => $league->country_code,
                    'events_count' => $league->events_count
                ]);
            }
        }
        return $this->successResponse($response, 200);

    }

    public function fetchPrematchLeagueEvents($league_id){
        if (!SportLeague::where('champ_id', $league_id)->first()){
            return $this->errorResponse([
                'error' => 'Request failed',
                'message' => 'There is no league with that champ_id'
            ], 422);
        }

        $response = $this->initiateFetchAllPrematchLeagueEvents($league_id);

        if ($response['errorCode'] !== "SUCCESS"){
            return $this->errorResponse($response, 422);
        }

        /*
         * Else if successful
         * Save response in the database if not exist
         */
//        $leagues  = json_encode($response); // Converts the response array to string
//        $leagues = json_decode($leagues); // Converts the sports string to object
//        foreach ($leagues->data as $league){
//            // Fetch from sports where the sport name exist
//            $leagueExist = SportLeague::where('name', $league->name)->first();
//            if (!$leagueExist){
//                SportLeague::create([
//                    'group_id'     => $group_id,
//                    'champ_id'     => $league->champ_id,
//                    'name'         => $league->name,
//                    'country_code' => $league->country_code,
//                    'events_count' => $league->events_count
//                ]);
//            }
//        }
        return $this->successResponse($response, 200);

    }
}
