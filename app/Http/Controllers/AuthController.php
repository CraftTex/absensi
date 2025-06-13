<?php

namespace App\Http\Controllers;

use App\Models\HRD;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request) {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'required'
        ]);

        
        $user = User::where('email', $validated['email'])->first();

        // dd($user);
        
        if (! $user ||  ! Hash::check($validated['password'], $user->password)) {
            return [
                'error' => ['The provided credentials are incorrect']
            ];
        }

        // dd();

        if (HRD::where('user_id', '=', $user->id)->exists()) {
            return response()->json([
                'success' => true,
                'data' => $user->createToken($validated['device_name'], ['member-hrd'])->plainTextToken
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $user->createToken($validated['device_name'])->plainTextToken
        ]);
    }

    public function register(Request $request) {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'name' => 'required',
            'nik' => 'required',
            'username' => 'required',
        ]);

        $userId = User::insertGetId([
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'name' => $validated['name'],
            'nik' => $validated['nik'],
            'username' => $validated['username'],
        ]);

        return response()->json([
            'success' => true,
            'data' => $userId
        ]);
    }

    public function logout(Request $request) {
        $count = $request->user()->tokens()->delete();
        return response()->json([
            'success' => true,
            'data' => $count
        ]);
    }
}
