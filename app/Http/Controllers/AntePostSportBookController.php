<?php

namespace App\Http\Controllers;

use App\Models\AntePostSportEventList;
use App\Models\AntePostSportGroup;
use App\Models\AntePostSportLeague;
use App\Models\AntePostSportList;
use App\Models\SportGroup;
use App\Models\SportLeague;
use App\Models\SportList;
use App\Traits\AuthHelpers\AuthUserManager;
use App\Traits\RequestHelpers\APIResponse;
use App\Traits\RequestHelpers\RemoteAPIServerAntePostSportBookActions;
use App\Traits\Utils\ArrayJsonManager;
use Illuminate\Http\Request;

class AntePostSportBookController extends Controller
{
    use APIResponse;
    use ArrayJsonManager;
    use AuthUserManager;
    use RemoteAPIServerAntePostSportBookActions;
    public $user;

    public function __construct(Request $request)
    {
        $this->user = $this->getCurrentUser($request);
    }

    public function fetchAntePostSports (){

        $response = $this->initiateFetchAllAntePostSports();

        if ($response['errorCode'] !== "SUCCESS"){
            return $this->errorResponse($response, 422);
        }

        /*
         * Else if successful
         * Sync response with the database
         */
        $this->syncSports($response);
        return $this->successResponse($response, 200);
    }

    public function fetchAntePostSportGroups($sport_id){
       if (!AntePostSportList::where('sport_id', $sport_id)->first()){
           return $this->errorResponse([
               'error' => 'Request failed',
               'message' => 'There is no sport with that sport_id'
           ], 422);
       }

        $response = $this->initiateFetchAllAntePostSportGroups($sport_id);

        if ($response['errorCode'] !== "SUCCESS"){
            return $this->errorResponse($response, 422);
        }

        /*
         * Else if successful
         * Sync response with the database
         */
        $this->syncSportGroups($response, $sport_id);
        return $this->successResponse($response, 200);

    }

    public function fetchAntePostGroupEvents($group_id){
        if (!AntePostSportGroup::where('group_id', $group_id)->first()){
            return $this->errorResponse([
                'error' => 'Request failed',
                'message' => 'There is no group with that group_id'
            ], 422);
        }

        $response = $this->initiateFetchAllAntePostGroupEvents($group_id);

        if ($response['errorCode'] !== "SUCCESS"){
            return $this->errorResponse($response, 422);
        }

        /*
         * Else if successful
         * Sync response with the database
         */
        $this->syncGroupEvents($response, $group_id);
        return $this->successResponse($response, 200);

    }

    public function fetchAntePostOddList($search_code){
        if (!AntePostSportEventList::where('search_code', $search_code)->first()){
            return $this->errorResponse([
                'error' => 'Request failed',
                'message' => 'There is no event with that search_id'
            ], 422);
        }

        $response = $this->initiateFetchAllAntePostOddLists($search_code);

        if ($response['errorCode'] !== "SUCCESS"){
            return $this->errorResponse($response, 422);
        }

        return $this->successResponse($response, 200);

    }


    /*
     * Sports book sync helpers
     */
    public function syncSports($sports){
        $sports  = $this->arrayToJson($sports); // Converts the response array to json
        foreach ($sports->data as $sport){
            // Fetch from sports where the sport name exist
            $sportExist = AntePostSportList::where('name', $sport->name)->first();
            if (!$sportExist){
                AntePostSportList::create([
                    'sport_id'     => $sport->sport_id,
                    'name'         => $sport->name,
                    'events_count' => $sport->events_count
                ]);
            }
        }
    }

    public function syncSportGroups($groups, $sport_id){
        $groups  = $this->arrayToJson($groups); // Converts the response array to json
        foreach ($groups->data as $group){
            // Fetch from sports where the sport name exist
            $groupExist = AntePostSportGroup::where('name', $group->name)->where('group_id', $group->group_id)->first();
            if (!$groupExist){
                AntePostSportGroup::create([
                    'sport_id'     => $sport_id,
                    'group_id'     => $group->group_id,
                    'name'         => $group->name,
                    'events_count' => $group->events_count
                ]);
            }
        }
    }

    public function syncGroupEvents($groups, $group_id){
        $groups  = $this->arrayToJson($groups); // Converts the response array to json
        foreach ($groups->data as $group){
            // Fetch from sports where the sport name exist
            $leagueExist = AntePostSportEventList::where('name', $group->name)->where('group_id', $group_id)->first();
            if (!$leagueExist){
                AntePostSportEventList::create([
                    'group_id'     => $group_id,
                    'search_code'  => $group->search_code,
                    'name'         => $group->name,
                    'startdate'    => $group->startdate,
                    'multiplicity' => $group->multiplicity,
                ]);
            }
        }
    }


    /*
     * Synchronizes the data in the db with remote server
     */
    public function syncPrematchSportBook() {
        $sportsArray = $this->initiateFetchAllPrematchSports();
        $sports = $this->arrayToJson($sportsArray);

        // Save new ones to the database
        $this->syncSports($sportsArray);

        // Loop through to save the groups
        foreach ($sports->data as $sport){
            // get the groups, save and Sync
           $groupsArray = $this->initiateFetchAllPrematchSportGroups($sport->sport_id);
           $groups = $this->arrayToJson($groupsArray);
           $this->syncSportGroups($groupsArray, $sport->sport_id);

            // get the leagues, save and Sync
            foreach ($groups->data as $group){
                $leaguesArray = $this->initiateFetchAllPrematchGroupLeagues($group->group_id);
                $leagues = $this->arrayToJson($leaguesArray);
                $this->syncGroupLeagues($leagues, $group->group_id);
            }
        }

        return $this->successResponse([
            'status' => 'Done',
            'message' => 'Sport book data synchronized successfully'
        ],200);

    }


}
