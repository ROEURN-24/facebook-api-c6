<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\User; // Import the User model

use Illuminate\Support\Facades\Log;


class AuthController extends Controller
{

    // User registration
    public function createUser(Request $request): JsonResponse
    {
        try {
            // Validate the request data
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:8',
                'email_verified_at' => 'nullable|date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Generate remember token
            $rememberToken = Str::random(60);

            // Create the new user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'remember_token' => $rememberToken,
                'email_verified_at' => $request->email_verified_at ?? null,
            ]);

            // Generate an authentication token for the newly created user
            $token = $user->createToken('auth_token')->plainTextToken;

            // Check if token is generated
            if (!$token) {
                Log::error('Token generation failed for user: ' . $user->email);
                return response()->json([
                    'status' => false,
                    'message' => 'Token generation failed',
                ], 500);
            }

            // Return the user data with the token
            return response()->json([
                'status' => true,
                'message' => 'User created successfully',
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'email_verified_at' => $user->email_verified_at,
                    'remember_token' => $user->remember_token,
                    'access_token' => $token,
                ],
                'token_type' => 'Bearer'
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    // User login
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid email or password'
            ], 401);
        }

        $user = $request->user();

        // Revoke existing tokens (if any)
        $user->tokens()->delete();

        // Generate a new authentication token for the user
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Login success',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }

    // Get user data
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        return response()->json([
            'status' => true,
            'message' => 'User data retrieved successfully',
            'user' => $user,
            'permissions' => $user->getAllPermissions(),
            'roles' => $user->getRoleNames()
        ]);
    }

    // Logout
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }

        try {
            // Revoke all tokens for the authenticated user
            $user->tokens()->delete();
        } catch (\Throwable $th) {
            // Handle any exceptions or errors during logout
            return response()->json([
                'status' => false,
                'message' => 'Error logging out. Please try again.'
            ], 500);
        }

        return response()->json([
            'status' => true,
            'message' => 'Logged out successfully',
            'user' => [
                'id' => $user->id,
                'email' => $user->email
            ]
        ]);
    }


}
