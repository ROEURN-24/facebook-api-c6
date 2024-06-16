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

        // Generate an OTP and set an expiry time (2 minutes from now)
        $otp = random_int(100000, 999999);
        $expiresAt = Carbon::now()->addMinutes(2);

        // Store the OTP and expiry time in the database
        $user->otp = $otp;
        $user->otp_expires_at = $expiresAt;
        $user->save();

        // Format the expiration time
        $expiresFormatted = $expiresAt->format('H:i:s');

        // Log the OTP (optional but recommended for auditing)
        Log::info("OTP generated for user {$user->email}: {$otp}");

        try {
            // Send email with OTP
            Mail::to($user->email)->send(new ResetPasswordMail($user, $otp, $expiresFormatted));

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
        'otp' => 'required|integer',
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

    // Find the user by email and OTP
    $user = User::where('email', $request->email)
        ->where('otp', $request->otp)
        ->first();

    if (!$user) {
        return response()->json([
            'status' => false,
            'message' => 'Invalid email or OTP'
        ], 400);
    }

    // Check if OTP is expired
    if (Carbon::parse($user->otp_expires_at)->isPast()) {
        return response()->json([
            'status' => false,
            'message' => 'OTP has expired'
        ], 400);
    }

    // Verify if the provided current password matches the user's actual password
    if (!Hash::check($request->current_password, $user->password)) {
        return response()->json([
            'status' => false,
            'message' => 'Current password does not match'
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

}
