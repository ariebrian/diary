<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function login(Request $request)
    {
        $user = User::where('username',$request->username)->first();
        if ($user) {
            if (Hash::check($request->password, $user->password)) {
                $token = rand(111111, 999999);

                User::where('username',$request->username)->update(['api_token' => $token]);

                return response()->json([
                    'name'         => $user->username,
                    'email'        => $user->email,
                    'access_token' => $token,
                  ]);
            }
            else {
                return response()->json([
                    'message' => 'Invalid password'
                ], 200);
            }
        } else {
            return response()->json([
                'message' => 'Invalid username'
            ], 401);
        }
    }

    public function register(Request $request)
    {

        $validated = $request->validate([
            'username' => 'required|unique:users|max:255',
            'password' => 'required|min:6|max:32|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&.,])[A-Za-z\d@$!%*?&.,]{6,}$/',
            'fullname' => 'required',
            'email' => 'required',
            'birthday' => 'required'
        ]);


        $user = new User;

        $user->username = $request->username;
        $user->password = Hash::make($request->password);
        $user->fullname = $request->fullname;
        $user->email = $request->email;
        $user->birthday = $request->birthday;

        $user->save();

        return response()->json([
            'name'         => $user->username,
            'email'        => $user->email,
            'fullname'     => $user->fullname,
            'birthday'     => $user->birthday,
        ], 201);
    }
}
