<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Follower;
use App\Models\User;
use Illuminate\Http\Request;

class FollowerController extends Controller
{
    /**
     * Follow a user.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function follow(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id|not_in:' . $request->user()->id,
        ]);

        $userId = $request->user_id;

        if ($request->user()->isFollowing($userId)) {
            return response()->json(['error' => 'Already following this user'], 400);
        }

        Follower::create([
            'user_id' => $userId,
            'follower_id' => $request->user()->id,
        ]);

        return response()->json(['message' => 'User followed successfully']);
    }

    /**
     * Unfollow a user.
     *
     * @param  Request  $request
     * @param  User  $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function unfollow(Request $request, User $user)
    {
        $authenticatedUser = $request->user();

        $followRecord = Follower::where('user_id', $user->id)
            ->where('follower_id', $authenticatedUser->id)
            ->first();

        if (!$followRecord) {
            return response()->json(['error' => 'Not following this user'], 400);
        }

        $followRecord->delete();

        return response()->json(['message' => 'User unfollowed successfully']);
    }

    /**
     * Get the list of followers for a user.
     *
     * @param  User  $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function followers(User $user)
    {
        $followers = $user->followers()->with('follower')->get();
        return response()->json($followers);
    }

    /**
     * Get the list of users a user is following.
     *
     * @param  User  $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function following(User $user)
    {
        $following = $user->following()->with('user')->get();
        return response()->json($following);
    }
}
