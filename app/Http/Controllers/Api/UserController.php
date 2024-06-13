<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required',
            'image' => 'nullable|mimes:png,jpg,jpeg,gif,webp'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors(),
                'succeed' => false,
            ], 400);
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imagePath = $image->store('users', 'public');
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'image' => $imagePath,
        ]);

        return response()->json([
            'message' => 'User created successfully',
            'succeed' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'image' => $imagePath ? Storage::url($imagePath) : null,
            ],
        ], 201);
    }



    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email'     => 'required|string|max:255',
            'password'  => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'User not found'
            ], 401);
        }

        $user   = User::where('email', $request->email)->firstOrFail();
        $token  = $user->createToken('auth_token')->plainTextToken;
        $imageUrl = $user->image ? Storage::url($user->image) : null;

        return response()->json([
            'message'       => 'Login success',
            'access_token'  => $token,
            'token_type'    => 'Bearer',
            'user' => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'image' => $imageUrl,
            ]
        ]);
    }


    public function logout(Request $request)
    {
        $user = $request->user();
        if ($user && $user->currentAccessToken) {
            $user->currentAccessToken->delete();
            return response()->json([
                'message' => 'User logged out successfully'
            ], 200);
        } else {
            return response()->json([
                'message' => 'User not found'
            ], 400);
        }
    }

    public function index()
    {
        dd(1);
        // $users = User::all();

        // return response()->json([
        //     'success' => true,
        //     'data' => $users->makeVisible(['image'])->map(function ($user) {
        //         return [
        //             'id' => $user->id,
        //             'name' => $user->name,
        //             'email' => $user->email,
        //             'image' => $user->image ? Storage::url($user->image) : null,
        //         ];
        //     })
        // ], 200);
    }
  
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();
        $imageUrl = $user->image ? Storage::url($user->image) : null;

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'image' => $imageUrl,
            ]
        ]);
    }

}
