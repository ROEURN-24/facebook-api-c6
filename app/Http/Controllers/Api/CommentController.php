<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use Illuminate\Http\Request;
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
        return response(['success' => true, 'data' =>$comments], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        // Validate the request
        $validator = Validator::make($request->all(), [
            'type' => 'required|string|in:text,image',
            'content' => 'required',
            'post_id' => 'required|exists:posts,id',
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Adjust the content based on type
        if ($request->type === 'image' && $request->hasFile('content')) {
            // Validate the image
            $validator = Validator::make($request->all(), [
                'content' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }
        }

        // Create or update the comment
        $comment = Comment::store($request);

        return response()->json(['message' => 'Comment created successfully', 'comment' => $comment], 201);
    // }
    //     Comment::store($request);
    //     return response()->json(['success' => true, 'message' => "Create comment successfully"], 200);


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
        Comment::store($request,$id);

        return ["success" => true, "Message" =>"Comment updated successfully"];

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {   
       $comment = Comment::find($id);
       $comment->delete();
        return ["success" => true, "Message" =>"Comment deleted successfully"];
    }

}
