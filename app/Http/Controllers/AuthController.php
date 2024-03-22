<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    function register(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'full_name' => 'required',
            'bio' => 'required|max:100',
            'username' => 'required|min:3|unique:users,username|regex:/^(?=.*[A-Z])(?=.*[._])[a-zA-Z0-9._]+$/',
            'password' => 'required|min:6',
            'is_private' => 'boolean'
        ],[
            'username.regex' => 'Username need to have atlest one Uppercase letter and ( . and - ) symbols and only that symbols allowed'
        ]);
        if($validator->fails()){
            return response()->json([
                'message' => 'Invalid field',
                'errors' => $validator->errors()
            ], 200);
        }
        
        $user = User::create([
            'full_name' => $request->full_name,
            'bio' => $request->bio,
            'username' => $request->username,
            'password' => bcrypt($request->password),
            'is_private' => $request->is_private
        ]);
        
        $credentials = $request->only(['username', 'password']);
        if(!auth()->attempt($credentials)){
            return response()->json([
                'message' => 'Username or password incorrect'
            ], 404);
        }

        $token = $user->createToken('SacntumToken')->plainTextToken;
        return response()->json([
            'message' => 'Register success',
            'token' => $token,
            'user' => $user
        ], 200);
    }

    function login(Request $request) {
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required',
        ]);

        if($validator->fails()) {
            return response()->json([
                'message' => 'Invalid field'
            ], 422);
        }

        $credentials = $request->only(['username', 'password']);
        if(!Auth::attempt($credentials)){
            return response()->json([
                'message' => 'Wrong username or password'
            ], 401);
        }

        $user = User::where('username', $request->username)->first();
        $token = $user->createToken('SacntumToken')->plainTextToken;
        $user->makeHidden("updated_at");

        return response()->json([
            'message' => 'Login success',
            'token' => $token,
            'user' => $user
        ], 200);
    }

    function logout(Request $request) {
        if($request->user()->tokens()->delete()){
            return response()->json([
                'message' => 'Logout success'
            ]);
        }
        return response()->json([
            'message' => 'Unauthenticated'
        ], 401);
    }
}
