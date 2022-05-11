<?php

namespace App\Http\Controllers;

use App\Models\OddsStructure;
use App\Models\RematchOddsStructure;
use App\Models\RematchOddsStructureGroup;
use App\Models\RematchOddsStructureGroupSign;
use App\Models\SportGroup;
use App\Models\SportLeague;
use App\Models\SportList;
//use App\Traits\AuthHelpers\AuthUserManager;
use App\Traits\RequestHelpers\APIResponse;
use App\Traits\RequestHelpers\RemoteAPIServerPrematchSportBookActions;
use App\Traits\Utils\ArrayJsonManager;
use Illuminate\Http\Request;

class PrematchSportBookController extends Controller
{
    use APIResponse;
    use ArrayJsonManager;
//    use AuthUserManager;
    use RemoteAPIServerPrematchSportBookActions;
//    public $user;
//
//    public function __construct(Request $request)
//    {
//        $this->user = $this->getCurrentUser($request);
//    }

    public function fetchPrematchSports (){

        $response = $this->initiateFetchAllPrematchSports();

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
         * Sync response with the database
         */
        $this->syncSportGroups($response, $sport_id);
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
         * Sync response with the database
         */
        $this->syncGroupLeagues($response, $group_id);
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

        if ($response['errorCode'] === "SUCCESS"){
            $this->syncPrematchOddsStructure($response['data']['oddstructure'], $league_id);
        }

        // The response contains the odds for each events
        return $this->successResponse($response, 200);
    }

    public function syncPrematchOddsStructure($oddsStructure, $league_id){
        $oddsStructure  = json_encode($oddsStructure); // Converts the response array to string
        $oddsStructure = json_decode($oddsStructure); // Converts the sports string to object

        // Save all the structures
        foreach ($oddsStructure as $structure){
            // Check if the structure Exist
           if (!RematchOddsStructure::where('champ_id', $league_id)->where('button_name', $structure->button_name)->first()){
              $stored_structure =  RematchOddsStructure::create([
                    'champ_id'      => $league_id,
                    'button_name'   => $structure->button_name
               ]);

              // Save the structure groups
              if ($structure->groups){
                  foreach ($structure->groups as $group){
                      if (!RematchOddsStructureGroup::where('odds_structure_id', $stored_structure->id)->where('group_name', $group->group_name)->first()){
                         $stored_group = RematchOddsStructureGroup::create([
                              'odds_structure_id'   => $stored_structure->id,
                              'group_name'          => $group->group_name
                          ]);

                          // Save the group signs
                          if ($group->signs){
                              foreach ($group->signs as $sign){
                                  if (!RematchOddsStructureGroupSign::where('odds_structure_group_id', $stored_group->id)->where('sign_key', $sign->sign_key)->where('sign_name', $sign->sign_name)->first()){
                                      RematchOddsStructureGroupSign::create([
                                          'odds_structure_group_id' => $stored_group->id,
                                          'sign_key'                => $sign->sign_key,
                                          'sign_name'               => $sign->sign_name
                                      ]);
                                  }
                              }
                          }

                      }
                  }
              }



           }
        }
    }


    public function fetchLocalPrematchSports (){

        $sportList = SportList::with('groups.leagues')->get();

        /*
         * Else if successful
         * Sync response with the database
         */
        return $this->successResponse($sportList, 200);
    }

    /*
     * Sports book sync helpers
     */

    public function syncSports($sports){
        $sports  = json_encode($sports); // Converts the response array to string
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
    }

    public function syncSportGroups($groups, $sport_id){
        $groups  = json_encode($groups); // Converts the response array to string
        $groups = json_decode($groups); // Converts the sports string to object
        foreach ($groups->data as $group){
            // Fetch from sports where the sport name exist
            $groupExist = SportGroup::where('name', $group->name)->where('group_id', $group->group_id)->first();
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
    }

    public function syncGroupLeagues($leagues, $group_id){
        $leagues  = json_encode($leagues); // Converts the response array to string
        $leagues = json_decode($leagues); // Converts the sports string to object
        foreach ($leagues->data as $league){
            // Fetch from sports where the sport name exist
            $leagueExist = SportLeague::where('name', $league->name)->where('group_id', $group_id)->first();
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
    }

    public function syncLeaguesOdds($oddsStructure, $league_id){
        $oddsStructure  = json_encode($oddsStructure); // Converts the response array to string
        $oddsStructure = json_decode($oddsStructure); // Converts the sports string to object
        // Save all the structures
        foreach ($oddsStructure as $structure){
            // Check if the structure Exist
            if (!RematchOddsStructure::where('champ_id', $league_id)->where('button_name', $structure->button_name)->first()){
                $stored_structure =  RematchOddsStructure::create([
                    'champ_id'      => $league_id,
                    'button_name'   => $structure->button_name
                ]);

                // Save the structure groups
                if ($structure->groups){
                    foreach ($structure->groups as $group){
                        if (!RematchOddsStructureGroup::where('odds_structure_id', $stored_structure->id)->where('group_name', $group->group_name)->first()){
                            $stored_group = RematchOddsStructureGroup::create([
                                'odds_structure_id'   => $stored_structure->id,
                                'group_name'          => $group->group_name
                            ]);

                            // Save the group signs
                            if ($group->signs){
                                foreach ($group->signs as $sign){
                                    if (!RematchOddsStructureGroupSign::where('odds_structure_group_id', $stored_group->id)->where('sign_key', $sign->sign_key)->where('sign_name', $sign->sign_name)->first()){
                                        RematchOddsStructureGroupSign::create([
                                            'odds_structure_group_id' => $stored_group->id,
                                            'sign_key'                => $sign->sign_key,
                                            'sign_name'               => $sign->sign_name
                                        ]);
                                    }
                                }
                            }

                        }
                    }
                }



            }
        }
    }

//    public function syncLeaguesEvents($events, $champ_id){
//        $leagues  = json_encode($leagues); // Converts the response array to string
//        $leagues = json_decode($leagues); // Converts the sports string to object
//        foreach ($leagues->data as $league){
//            // Fetch from sports where the sport name exist
//            $leagueExist = SportLeague::where('name', $league->name)->where('group_id', $group_id)->first();
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
//    }


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
            set_time_limit(0);
            // get the groups, save and Sync
           $groupsArray = $this->initiateFetchAllPrematchSportGroups($sport->sport_id);
           $groups = $this->arrayToJson($groupsArray);

            // Save new ones to the database
           $this->syncSportGroups($groupsArray, $sport->sport_id);

            // get the leagues, save and Sync
            foreach ($groups->data as $group){
                $leaguesArray = $this->initiateFetchAllPrematchGroupLeagues($group->group_id);
                $leagues = $this->arrayToJson($leaguesArray);
                // Save new ones to the database
                $this->syncGroupLeagues($leagues, $group->group_id);

                foreach ($leagues->data as $league){
                    $eventsArray = $this->initiateFetchAllPrematchLeagueEvents($league->champ_id);
                    $events = $this->arrayToJson($eventsArray);

                   foreach ($events->data->oddstructure as $structure){
                       $this->syncLeaguesOdds($structure, $league->champ_id);
                   }

                }

            }

        }

        return $this->successResponse([
            'status' => 'Done',
            'message' => 'Sport book data synchronized successfully'
        ],200);

    }


}
