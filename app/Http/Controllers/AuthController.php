<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // validate
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
        ]);

        // create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user
        ], 201);
    }

    public function login(Request $request)
    {
       
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

       
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        $user = Auth::user();

        return response()->json([
            'message' => 'Login successful',
            'user' => $user
        ]);
    }

public function resetPassword(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'pin' => 'required',
        'password' => 'required|min:6'
    ]);

    $user = User::where('email', $request->email)->first();

    if (!$user) {
        return response()->json(['message' => 'User not found'], 404);
    }

    // check PIN
   // check PIN ដោយ plain text comparison
if ($request->pin != $user->pin) {
    return response()->json(['message' => 'Invalid PIN'], 401);
}

    // update password
    $user->password = Hash::make($request->password);
    $user->save();

    return response()->json([
        'message' => 'Password reset successful'
    ]);
}


public function forgotPassword(Request $request)
{
    $request->validate([
        'email' => 'required|email',
    ]);

    $user = User::where('email', $request->email)->first();

    if (!$user) {
        return response()->json(['message' => 'User not found'], 404);
    }

    // Generate PIN 4 digits
    $pin = rand(1000, 9999);

    // Save PIN to DB
    $user->pin = $pin;
    $user->save();


    return response()->json([
        'message' => 'PIN sent to your email',
        'pin' => $pin 
    ]);
}

}