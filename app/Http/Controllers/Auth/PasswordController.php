<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Mail\ResetPasswordMail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class PasswordController extends Controller
{
    /**
     * Change user password.
     */
    public function changePassword(Request $request): JsonResponse
    {
        // Validate incoming request data
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'current_password' => 'required|string|min:8',
            'new_password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        // Retrieve the user based on email
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return $this->userNotFoundResponse();
        }

        if (!Hash::check($request->current_password, $user->password)) {
            return $this->incorrectCurrentPasswordResponse();
        }

        // Update the password
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Password changed successfully'
        ]);
    }

    /**
     * Handle forgot password request.
     */
    public function forgetPassword(Request $request): JsonResponse
    {
        // Validate incoming request data
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        $user = User::where('email', $request->email)->first();

        // Return success for non-existing user to prevent email enumeration attacks
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

        // Generate OTP and set expiry time
        $otp = $this->generateOtp();
        $expiresAt = Carbon::now()->addMinutes(2);
        $expiresFormatted = $expiresAt->format('H:i:s');

        // Store the OTP and expiry time in the database
        $user->otp = $otp;
        $user->otp_expires_at = $expiresAt;
        $user->save();

        Log::info("OTP generated for user {$user->email}: {$otp}");

        try {
            Mail::to($user->email)->send(new ResetPasswordMail($user, $otp, $expiresFormatted));
            return response()->json([
                'status' => true,
                'message' => 'Password reset email sent successfully'
            ]);
        } catch (\Throwable $th) {
            Log::error("Failed to send password reset email to {$user->email}: {$th->getMessage()}");
            return $this->emailSendingErrorResponse();
        }
    }

    /**
     * Handle password reset request.
     */
    public function resetPassword(Request $request): JsonResponse
    {
        // Validate incoming request data
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp' => 'required|integer',
            'new_password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        $user = User::where('email', $request->email)
                    ->where('otp', $request->otp)
                    ->first();

        if (!$user || $this->isOtpExpired($user)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid email or OTP'
            ], 400);
        }

        // Update the user's password and clear the OTP
        $user->password = Hash::make($request->new_password);
        $user->otp = null;
        $user->otp_expires_at = null;
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Password reset successfully'
        ]);
    }

    /**
     * Generate a random OTP.
     */
    private function generateOtp(): int
    {
        return random_int(100000, 999999);
    }

    /**
     * Check if the OTP has expired.
     */
    private function isOtpExpired(User $user): bool
    {
        return Carbon::parse($user->otp_expires_at)->isPast();
    }

    /**
     * Return a validation error response.
     */
    private function validationErrorResponse($validator): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => 'Validation error',
            'errors' => $validator->errors()
        ], 422);
    }

    /**
     * Return a user not found response.
     */
    private function userNotFoundResponse(): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => 'User not found'
        ], 404);
    }

    /**
     * Return an incorrect current password response.
     */
    private function incorrectCurrentPasswordResponse(): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => 'Current password is incorrect'
        ], 400);
    }

    /**
     * Return an email sending error response.
     */
    private function emailSendingErrorResponse(): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => 'Failed to send password reset email'
        ], 500);
    }
}
