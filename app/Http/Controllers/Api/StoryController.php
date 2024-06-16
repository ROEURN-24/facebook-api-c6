<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Story;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class StoryController extends Controller
{
    /**
     * Create a new story.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        $request->validate([
            'content' => 'required|string',
            'visibility' => 'required|string|in:public,friends,private',
        ]);

        $story = Story::create([
            'user_id' => $request->user()->id,
            'content' => $request->content,
            'expires_at' => Carbon::now()->addHours(24),
            'visibility' => $request->visibility,
        ]);

        return response()->json(['message' => 'Story created successfully', 'story' => $story]);
    }

    /**
     * View stories of a user.
     *
     * @param  Request  $request
     * @param  User  $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function userStories(Request $request, User $user)
    {
        $stories = Story::visibleTo($request->user())
                        ->where('user_id', $user->id)
                        ->get();

        return response()->json($stories);
    }

    /**
     * View all stories.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function allStories(Request $request)
    {
        $stories = Story::visibleTo($request->user())->with('user')->get();
        return response()->json($stories);
    }

    /**
     * Delete a story.
     *
     * @param  Request  $request
     * @param  Story  $story
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request, Story $story)
    {
        if ($request->user()->id !== $story->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $story->delete();
        return response()->json(['message' => 'Story deleted successfully']);
    }
}
