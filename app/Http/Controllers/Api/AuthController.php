<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Checks the credentials sent by the user then returns a new api token if the credentials are correct.
     * Returns a 400 error otherwhise. 
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    public function login(Request $request)
    {
        // validate the info, create rules for the inputs
        $rules = array(
            'email'    => 'required|email', // make sure the email is an actual email
            'password' => 'required|alphaNum|min:3' // password can only be alphanumeric and has to be greater than 3 characters
        );

        $validator = \Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 400);
        }

        // create our user data for the authentication
        $credentials = [
            'email'     => $request->get('email'),
            'password'  => $request->get('password')
        ];

        $user = User::where('email', $credentials['email'])->first();

        // Check the user exists in the database and matches the given password.
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json('The provided credentials do not match our records.', 400);
        }
       
        // Returns a new api token.
        return response()->json(['api_token' => $user->updateApiToken()]);
    }
}
