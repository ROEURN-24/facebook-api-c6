<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $comments = Comment::list();
        return response(['success' => true, 'data' =>$comments], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Comment::store($request);
        return response()->json(['success' => true, 'message' => "Create comment successfully"], 200);
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
