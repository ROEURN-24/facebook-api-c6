<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\User;
use App\Mail\ResetPasswordMail;
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
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => true,
            'message' => 'Logged out successfully'
        ]);
    }

    // Update User Details
    public function updateUser(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $request->user()->id,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $user->update($request->only('name', 'email'));

        return response()->json([
            'status' => true,
            'message' => 'User details updated successfully',
            'user' => $user
        ]);
    }
    // Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException
    // Change Password
    // public function changePassword(Request $request): JsonResponse
    // {
    //     // Validate incoming request data
    //     $validator = Validator::make($request->all(), [
    //         'id' => 'required|integer',
    //         'email' => 'required|email',
    //         'current_password' => 'required|string|min:8',
    //         'new_password' => 'required|string|min:8',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Validation error',
    //             'errors' => $validator->errors()
    //         ], 422);
    //     }

    //     // Retrieve the user based on provided id and email
    //     $user = User::where('id', $request->id)
    //     ->where('email', $request->email)
    //     ->first();

    //     // Check if user exists
    //     if (!$user) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'User not found'
    //         ], 404);
    //     }

    //     // Check if the current password matches
    //     if (!Hash::check($request->current_password, $user->password)) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Current password is incorrect'
    //         ], 400);
    //     }

    //     // Update the password
    //     $user->password = Hash::make($request->new_password);
    //     $user->save();

    //     return response()->json([
    //         'status' => true,
    //         'message' => 'Password changed successfully'
    //     ]);
    // }

    public function changePassword(Request $request): JsonResponse
    {
        // Validate incoming request data
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'current_password' => 'required|string|min:8',
            'new_password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Retrieve the user based on email
        $user = User::where('email', $request->email)->first();

        // Check if user exists
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }

        // Check if the current password matches
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Current password is incorrect'
            ], 400);
        }

        // Update the password
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Password changed successfully'
        ]);
    }

    // Forget Password

    // public function forgetPassword(Request $request): JsonResponse
    // {
    //     $validator = Validator::make($request->all(), [
    //         'email' => 'required|email'
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Validation error',
    //             'errors' => $validator->errors()
    //         ], 422);
    //     }

    //     $user = User::where('email', $request->email)->first();

    //     // If the user doesn't exist, always return a success message to prevent email enumeration attacks
    //     if (!$user) {
    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Password reset email sent successfully'
    //         ]);
    //     }

    //     // Generate a reset token
    //     $token = Str::random(60);
    //     $user->reset_password_token = $token;
    //     $user->save();

    //     // Log the reset token
    //     Log::info("Reset token generated for user {$user->email}: {$token}");

    //     // Send email with password reset link
    //     Mail::to($user->email)->send(new ResetPasswordMail($user, $token));

    //     return response()->json([
    //         'status' => true,
    //         'message' => 'Password reset email sent successfully'
    //     ]);
    // }

    public function forgetPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        // If the user doesn't exist, always return a success message to prevent email enumeration attacks
        if (!$user) {
            return response()->json([
                'status' => true,
                'message' => 'Password reset email sent successfully'
            ]);
        }

        // Generate a reset token
        $token = Str::random(60);
        $user->reset_password_token = $token;
        $user->save();

        // Log the reset token (optional but recommended)
        Log::info("Reset token generated for user {$user->email}: {$token}");

        // Send email with password reset link
        Mail::to($user->email)->send(new ResetPasswordMail($user, $token));

        return response()->json([
            'status' => true,
            'message' => 'Password reset email sent successfully'
        ]);
    }


    // Reset Password
    public function resetPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'token' => 'required|string',
            'new_password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)
            ->where('reset_password_token', $request->token)
            ->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid email or token'
            ], 400);
        }

        $user->password = Hash::make($request->new_password);
        $user->reset_password_token = null;
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Password reset successfully'
        ]);
    }

    // Delete Account
    public function deleteUser(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->delete();

        return response()->json([
            'status' => true,
            'message' => 'Account deleted successfully'
        ]);
    }

    // Upload Profile and Background Images
    public function uploadImages(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'profile_image' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
            'background_image' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        if ($request->hasFile('profile_image')) {
            $profileImagePath = $request->file('profile_image')->store('profile_images', 'public');
            $user->profile_image = $profileImagePath;
        }

        if ($request->hasFile('background_image')) {
            $backgroundImagePath = $request->file('background_image')->store('background_images', 'public');
            $user->background_image = $backgroundImagePath;
        }

        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Images uploaded successfully',
            'user' => $user
        ]);
    }
}
