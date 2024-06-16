<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Http\Resources\ShowPostResource;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{

    public function index()
    {
        $posts = Post::list();
        $posts = PostResource::collection($posts);
        return response(['success' => true, 'data' => $posts], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $post = Post::store($request);

        return response()->json(['message' => 'Post created successfully', 'post' => $post], 201);
    }

    public function show(string $id)
    {
        $post = Post::find($id);
        $post = new ShowPostResource($post);
        return response()->json(['success' => true, 'data' => $post], 200);
    }

    public function update(Request $request, string $id)
    {
        Post::store($request, $id);
        return response()->json(['success' => true, 'message' => 'Post updated successfully'], 200);
    }

    public function destroy(string $id)
    {
        $post = Post::find($id);
        $post->delete();
        return ["success" => true, "Message" => "Post deleted successfully"];
    }
    
}
