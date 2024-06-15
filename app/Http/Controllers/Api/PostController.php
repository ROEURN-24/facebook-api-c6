<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $posts = Post::list();
        $posts = PostResource::collection($posts);
        return response(['success' => true, 'data' =>$posts], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Handle image upload
        $image = null;
        if ($request->hasFile('image')) {
            // Store the uploaded file
            $image = $request->file('image');
            $path = $image->store('image', 'public');
            $path = Storage::url($path);
        }
        $post = Post::create([
            'title' => $request->title,
            'user_id' => $request->user_id,
            'image' => isset($path) ? $path : null,
        ]);


        // $post = Post::create([
        //     'title' => $request->title,
        //     'image' => $image,
        //     'user_id' => $request->user_id,
        // ]);

        return response()->json(['message' => 'Post created successfully', 'post' => $post], 201);
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
       $post = Post::find($id);
       return response()->json(['success' => true, 'data' => $post], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        Post::store($request,$id);

        return ["success" => true, "Message" =>"Post updated successfully"];

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {   
       $post = Post::find($id);
       $post->delete();
        return ["success" => true, "Message" =>"Post deleted successfully"];
    }
}
