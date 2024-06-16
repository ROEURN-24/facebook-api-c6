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

    public function index(Request $request)
    {
        $likes = Like::list();
        $likes = LikeResource::collection($likes);
        return response(['success' => true, 'data' => $likes], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'like_number' => 'required',
            'type' => 'required|string|in:like,love,haha,angry',
            'post_id' => 'required|exists:posts,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = auth()->user(); 

        $existingLike = Like::where('post_id', $request->post_id)
            ->where('user_id', $user->id)
            ->first();

        if ($existingLike) {
            $existingLike->delete([
                'type' => $request->type,
                'image' => $this->storeImage($request),
            ]);

            return response()->json(['message' => 'User unliked successfully'], 200);
        }

        $like = Like::create([
            'like_number' => $request->like_number,
            'type' => $request->type,
            'post_id' => $request->post_id,
            'user_id' => $user->id,
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
