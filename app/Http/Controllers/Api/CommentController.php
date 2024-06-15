<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

// use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $comments = Comment::list();
        $comments = CommentResource::collection($comments);
        return response(['success' => true, 'data' => $comments], 200);
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(Request $request)
    {
        // Basic validation for 'type', 'post_id', and 'user_id'
        $validator = Validator::make($request->all(), [
            'type' => 'required|string|in:text,image',
            'post_id' => 'required|exists:posts,id',
            'user_id' => 'required|exists:users,id',
        ]);

        // Check for basic validation errors first
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Additional validation for 'content' based on 'type'
        if ($request->type === 'text') {
            $validator = Validator::make($request->all(), [
                'content' => 'required|string',
            ]);
        } elseif ($request->type === 'image') {
            $validator = Validator::make($request->all(), [
                'content' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);
        }

        // Check for content validation errors
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Handle image upload if the type is image
        $content = $request->input('content');
        if ($request->type === 'image' && $request->hasFile('content')) {
            // Store the uploaded file
            $image = $request->file('content');
            $path = $image->store('images', 'public');
            $content = Storage::url($path);
        }

        // Create the comment
        $comment = Comment::create([
            'type' => $request->type,
            'content' => $content,
            'post_id' => $request->post_id,
            'user_id' => $request->user_id,
        ]);

        return response()->json(['message' => 'Comment created successfully', 'comment' => $comment], 201);
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $comment = Comment::find($id);
        return response()->json(['success' => true, 'data' => $comment], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        Comment::store($request, $id);

        return ["success" => true, "Message" => "Comment updated successfully"];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $comment = Comment::find($id);
        $comment->delete();
        return ["success" => true, "Message" => "Comment deleted successfully"];
    }
}
