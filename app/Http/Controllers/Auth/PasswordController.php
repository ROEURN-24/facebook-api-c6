<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\User;
use App\Mail\ResetPasswordMail;
use Carbon\Carbon;

class PasswordController extends Controller
{
    // Change Password
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

        // Find the user by email
        $user = User::where('email', $request->email)->first();

        // If the user doesn't exist, return success to prevent email enumeration attacks
        if (!$user) {
            return response()->json([
                'status' => true,
                'message' => 'Password reset email sent successfully'
            ]);
        }

        // Check if the user is logged in
        if (Auth::check()) {
            return response()->json([
                'status' => false,
                'message' => 'Cannot reset password while logged in'
            ], 400);
        }

        // Generate a reset token and set an expiry time (5 minutes from now)
        $token = Str::random(60);
        $expiresAt = Carbon::now()->addMinutes(5);
        $formattedTime = $expiresAt->diffForHumans(now(), ['parts' => 2]);

        // Store the token and expiry time in the database
        $user->reset_password_token = $token;
        $user->reset_password_token_expires_at = $expiresAt;
        $user->save();

        // Log the reset token (optional but recommended for auditing)
        Log::info("Reset token generated for user {$user->email}: {$token}");

        try {
            // Send email with password reset link
            Mail::to($user->email)->send(new ResetPasswordMail($user, $token, $formattedTime));

            return response()->json([
                'status' => true,
                'message' => 'Password reset email sent successfully'
            ]);
        } catch (\Throwable $th) {
            // Handle any exceptions that occur during email sending
            Log::error("Failed to send password reset email to {$user->email}: {$th->getMessage()}");

            return response()->json([
                'status' => false,
                'message' => 'Failed to send password reset email'
            ], 500);
        }
    }

    // Reset Password
    public function resetPassword(Request $request): JsonResponse
    {
        // Validate the request inputs
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'token' => 'required|string',
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

        // Find the user by email and token
        $user = User::where('email', $request->email)
            ->where('reset_password_token', $request->token)
            ->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid email or token'
            ], 400);
        }

        // Verify if the provided current password matches the user's actual password
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Current password does not match'
            ], 400);
        }

        // Update the user's password and clear the reset token
        $user->password = Hash::make($request->new_password);
        $user->reset_password_token = null;
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Password reset successfully'
        ]);
    }
}
