<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
            'user' => $user->fresh(), // Ensure to fetch updated user data
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

        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            $profileImage = $request->file('profile_image');
            $profileImagePath = $this->storeUniqueImage($profileImage, 'profile_images', 'public');
            $user->profile_image = $profileImagePath;
        }

        // Handle background image upload
        if ($request->hasFile('background_image')) {
            $backgroundImage = $request->file('background_image');
            $backgroundImagePath = $this->storeUniqueImage($backgroundImage, 'background_images', 'public');
            $user->background_image = $backgroundImagePath;
        }

        $user->save();

        // Construct full URLs for images
        $user->profile_image = $this->getImageUrl($user->profile_image);
        $user->background_image = $this->getImageUrl($user->background_image);

        return response()->json([
            'status' => true,
            'message' => 'Images uploaded successfully',
            'user' => $user
        ]);
    }

    // Helper function to store unique image file
    private function storeUniqueImage($file, $directory, $disk)
    {
        // Generate a unique filename
        $filename = Str::random(40) . '.' . $file->getClientOriginalExtension();

        // Calculate checksum (MD5 hash) of the file content
        $checksum = md5_file($file->getRealPath());

        // Check if an image with the same checksum already exists
        $existingFiles = Storage::disk($disk)->allFiles($directory);
        foreach ($existingFiles as $existingFile) {
            $existingFilePath = storage_path('app/' . $existingFile);
            if (md5_file($existingFilePath) === $checksum) {
                // Return the existing filename if content matches
                return $directory . '/' . pathinfo($existingFile, PATHINFO_FILENAME) . '.' . $file->getClientOriginalExtension();
            }
        }

        // Store the file with the generated unique filename
        return $file->storeAs($directory, $filename, $disk);
    }

    // Helper function to get full URL for images
    private function getImageUrl($imagePath)
    {
        if (!$imagePath) {
            return null;
        }

        return url(Storage::url($imagePath));
    }
}
