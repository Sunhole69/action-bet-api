<?php

namespace App\Http\Controllers;

use App\Models\User;

use App\Traits\AuthHelpers\AuthUserManager;
use App\Traits\RequestHelpers\APIResponse;
use App\Traits\RequestHelpers\RemoteAPIServerPlayerActions;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use APIResponse;
    use AuthUserManager;
    use RemoteAPIServerPlayerActions;

    public $user;

    public function __construct(Request $request)
    {
        $this->user = $this->getCurrentUser($request);
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $request->validate([
            'parent' => 'required|string|max:255',
            'page'  => 'required|numeric|min:1',
        ]);

        // Fetch user info online
        $data = [
            'parent'    => $request->parent,
            'page'      => $request->page
        ];

        $response =  $this->fetchAllAgentPlayersRemotely($data);
        // Use the returned token for the user details request

        return $this->successResponse($response, 200);
    }


    /**
     * Display the specified resource.
     *
     * @param  User  $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        // Fetch user info locally
        $user = User::find($id);
        if (!$user){
            return $this->errorResponse(array([
                'message' => 'User with that id not found'
            ]), 422);
        }

        // Fetch user info online
        $data = [
            'username'  => $user->username,
        ];
        $response =  $this->fetchPlayerDetailsRemotely($data);
        // Use the returned token for the user details request
        return $this->successResponse($response, 200);
    }

    public function showMe(){
        // Fetch user info online
        $data = [
            'username'  => $this->user->username,
        ];
        $response =  $this->fetchPlayerDetailsRemotely($data);
        // Use the returned token for the user details request
        return $this->successResponse($response, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Fetch user info locally
        $user = User::find($id);
    }

    public function updatePassword(Request $request, $id)
    {
        // Fetch user info locally
        $user = User::find($id);
        if (!$user){
            return $this->errorResponse(array([
                'message' => 'User with that id not found'
            ]), 422);
        }
        //1. Validating user inputs
        $request->validate([
            'password'  => 'required|string|confirmed'
        ]);

        $data = [
            'username' => $user->username,
            'password' => $request->password
        ];

        //Update remotely, if successful, update locally
        $response = $this->updateUserPasswordRemotely($data);

        // If successful, save the new user password in local database
        if ($response['errorCode'] === "SUCCESS"){
            $user->update([
                'password' => $request->password
            ]);
        }

        //Finally return the server response
        return $this->successResponse($response, 200);

    }

    public function updateUserStatus(Request $request, $id)
    {
        // Fetch user info locally
        $user = User::find($id);
        if (!$user){
            return $this->errorResponse(array([
                'message' => 'User with that id not found'
            ]), 422);
        }
        //1. Validating user inputs
        $request->validate([
            'status'  => 'required|string'
        ]);

        if (strtolower($request->status !== 'true') && strtolower($request->status !== 'false'))
        {
            return $this->errorResponse(array([
                'message' => 'status can either be true or false (string)'
            ]), 422);
        }

        $data = [
            'username' => $user->username,
            // Converting the strings to boolean equivalent
            'status'   => (strtolower($request->status === 'true'))  ? true: false
        ];

        //Update remotely, if successful, update locally
        $response = $this->updateUserStatusRemotely($data);

        // If successful, save the new user password in local database
        if ($response['errorCode'] === "SUCCESS"){
            $user->update([
                'password' => $request->password
            ]);
        }

        //Finally return the server response
        return $this->successResponse($response, 200);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        //Delete user remotely first

        $user->delete();
        return $this->successResponse('User deleted successfully', 200);
    }
}
