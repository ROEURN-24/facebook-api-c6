<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
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
