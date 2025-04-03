<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\UserRegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(UserRegisterRequest $request)
    {
        try {
            $validated = $request->validated();

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            auth()->login($user);

            return UserResource::make($user)
                ->additional([
                    'message' => 'User registered successfully',
                ])->response()
                ->setStatusCode(201);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error registering user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function login(LoginRequest $request)
    {
        try {
            if (!auth()->attempt($request->only('email', 'password'))) {
                throw ValidationException::withMessages([
                    'msg' => __('auth.failed')
                ]);
            }

            $request->session()->regenerate();

            return response()->noContent();

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error logging in',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
