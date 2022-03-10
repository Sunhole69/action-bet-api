<?php

namespace App\Http\Controllers;

use App\Models\SpecialSportEventList;
use App\Models\SpecialSportGroup;
use App\Models\SpecialSportLeague;
use App\Models\SpecialSportList;
use App\Models\SportGroup;
use App\Models\SportLeague;
use App\Models\SportList;
use App\Traits\AuthHelpers\AuthUserManager;
use App\Traits\RequestHelpers\APIResponse;
use App\Traits\RequestHelpers\RemoteAPIServerSpecialSportBookActions;
use App\Traits\Utils\ArrayJsonManager;
use Illuminate\Http\Request;

class SpecialSportBookController extends Controller
{
    use APIResponse;
    use ArrayJsonManager;
    use AuthUserManager;
    use RemoteAPIServerSpecialSportBookActions;
    public $user;

    public function __construct(Request $request)
    {
        $this->user = $this->getCurrentUser($request);
    }

    public function fetchSpecialSports (){

        $response = $this->initiateFetchAllSpecialSports();

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

    public function fetchSpecialSportGroups($sport_id){
        if (!SpecialSportList::where('sport_id', $sport_id)->first()){
            return $this->errorResponse([
                'error' => 'Request failed',
                'message' => 'There is no sport with that sport_id'
            ], 422);
        }

        $response = $this->initiateFetchAllSpecialSportGroups($sport_id);

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

    public function fetchSpecialGroupLeagues($group_id){
        if (!SpecialSportGroup::where('group_id', $group_id)->first()){
            return $this->errorResponse([
                'error' => 'Request failed',
                'message' => 'There is no group with that group_id'
            ], 422);
        }

        $response = $this->initiateFetchAllSpecialGroupLeagues($group_id);

        if ($response['errorCode'] !== "SUCCESS"){
            return $this->errorResponse($response, 422);
        }

        /*
         * Else if successful
         * Sync response with the database
         */
        $this->syncGroupLeagues($response, $group_id);
        return $this->successResponse($response, 200);

    }

    public function fetchSpecialLeagueEvents($league_id){
        $league = SpecialSportLeague::where('champ_id', $league_id)->first();
        if (!$league){
            return $this->errorResponse([
                'error' => 'Request failed',
                'message' => 'There is no league with that champ_id'
            ], 422);
        }

        $response = $this->initiateFetchAllSpecialLeagueEvents($league);

        if ($response['errorCode'] !== "SUCCESS"){
            return $this->errorResponse($response, 422);
        }

        $this->syncGroupEvents($response, $league->group_id);
        // The response contains the odds for each events
        return $this->successResponse($response, 200);
    }

    public function fetchSpecialOddList($search_code){
        if (!SpecialSportEventList::where('search_code', $search_code)->first()){
            return $this->errorResponse([
                'error' => 'Request failed',
                'message' => 'There is no event with that search_id'
            ], 422);
        }

        $response = $this->initiateFetchAllSpecialOddLists($search_code);

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
            $sportExist = SpecialSportList::where('name', $sport->name)->first();
            if (!$sportExist){
                SpecialSportList::create([
                    'sport_id'     => $sport->sport_id,
                    'name'         => $sport->name,
                    'events_count' => $sport->events_count
                ]);
            }
        }
    }

    public function syncSportGroups($groups, $sport_id){
        $groups  = $this->arrayToJson($groups); // Converts the response array to string
        foreach ($groups->data as $group){
            // Fetch from sports where the sport name exist
            $groupExist = SpecialSportGroup::where('name', $group->name)->where('group_id', $group->group_id)->first();
            if (!$groupExist){
                SpecialSportGroup::create([
                    'sport_id'     => $sport_id,
                    'group_id'     => $group->group_id,
                    'name'         => $group->name,
                    'events_count' => $group->events_count
                ]);
            }
        }
    }

    public function syncGroupLeagues($leagues, $group_id){
        $leagues  = $this->arrayToJson($leagues); // Converts the response array to string
        foreach ($leagues->data as $league){
            // Fetch from sports where the sport name exist
            $leagueExist = SpecialSportLeague::where('name', $league->name)->where('group_id', $group_id)->first();
            if (!$leagueExist){
                SpecialSportLeague::create([
                    'group_id'     => $group_id,
                    'champ_id'     => $league->champ_id,
                    'name'         => $league->name,
                    'events_count' => $league->events_count
                ]);
            }
        }
    }

    public function syncGroupEvents($groups, $group_id){
        $groups  = $this->arrayToJson($groups); // Converts the response array to json
        foreach ($groups->data as $group){
            // Fetch from sports where the sport name exist
            $leagueExist = SpecialSportEventList::where('name', $group->name)->where('group_id', $group_id)->first();
            if (!$leagueExist){
                SpecialSportEventList::create([
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
    public function syncSpecialSportBook() {
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
