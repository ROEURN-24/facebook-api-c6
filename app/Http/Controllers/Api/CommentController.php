<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{

    public function index(Request $request)
    {
        $comments = Comment::list();
        $comments = CommentResource::collection($comments);
        return response(['success' => true, 'data' => $comments], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|string|in:text,image',
            'post_id' => 'required|exists:posts,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        if ($request->type === 'text') {
            $validator = Validator::make($request->all(), [
                'content' => 'required|string',
            ]);
        } elseif ($request->type === 'image') {
            $validator = Validator::make($request->all(), [
                'content' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);
        }

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $content = $request->input('content');
        if ($request->type === 'image' && $request->hasFile('content')) {
            $image = $request->file('content');
            $path = $image->store('comments', 'public');
            $content = Storage::url($path);
        }

        $comment = Comment::create([
            'type' => $request->type,
            'content' => $content,
            'post_id' => $request->post_id,
            'user_id' => $request->user()->id,
        ]);

        return response()->json(['message' => 'Comment created successfully', 'comment' => $comment], 201);
    }

    public function show(string $id)
    {
        $comment = Comment::find($id);
        return response()->json(['success' => true, 'data' => $comment], 200);
    }

    public function update(Request $request, string $id)
    {
        Comment::store($request, $id);

        return ["success" => true, "Message" => "Comment updated successfully"];
    }

    public function destroy(string $id)
    {
        $comment = Comment::find($id);
        $comment->delete();
        return ["success" => true, "Message" => "Comment deleted successfully"];
    }
    
}
