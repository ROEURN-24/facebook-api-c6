<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\LikeResource;
use App\Models\Like;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class LikeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $likes = Like::list();
        $likes = LikeResource::collection($likes);
        return response(['success' => true, 'data' => $likes], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'like_number' => 'required',
            'type' => 'required|string|in:like,love,haha,angry',
            'post_id' => 'required|exists:posts,id',
            'user_id' => 'required|exists:users,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Check if the user has already liked the post
        $existingLike = Like::where('post_id', $request->post_id)
            ->where('user_id', $request->user_id)
            ->first();

        if ($existingLike) {
            // If the user has already liked the post, update the like
            $existingLike->delete([
                'type' => $request->type,
                'image' => $this->storeImage($request),
            ]);

            return response()->json(['message' => 'User unlike successfully', 'like' => $existingLike], 200);
        }

        // Create the new like
        $like = Like::create([
            'like_number' => $request->like_number,
            'type' => $request->type,
            'post_id' => $request->post_id,
            'user_id' => $request->user_id,
            'image' => $this->storeImage($request),
        ]);

        return response()->json(['message' => 'Like created successfully', 'like' => $like], 201);
    }


    public function show(string $id)
    {
       $like = Like::find($id);
       return response()->json(['success' => true, 'data' => $like], 200);
    }

    private function storeImage(Request $request)
    {
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $path = $request->file('image')->store('likes', 'public');
            return Storage::url($path);
        }

        return null;
    }


}
