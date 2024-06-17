<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Friend;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FriendController extends Controller
{
    /**
     * Unfriend a user.
     *
     * @param  Request  $request
     * @param  User  $friend
     * @return \Illuminate\Http\JsonResponse
     */
    public function unfriend(Request $request, User $friend)
    {
        $authenticatedUser = $request->user();

        // Find and delete the friendship entry
        $friendship = Friend::where(function ($query) use ($authenticatedUser, $friend) {
            $query->where('user_id', $authenticatedUser->id)
                  ->where('friend_id', $friend->id);
        })->orWhere(function ($query) use ($authenticatedUser, $friend) {
            $query->where('user_id', $friend->id)
                  ->where('friend_id', $authenticatedUser->id);
        })->first();

        if ($friendship) {
            $friendship->delete();
            return response()->json(['message' => 'Friend deleted']);
        } else {
            return response()->json(['error' => 'User is not a friend'], 400);
        }
    }

    /**
     * Block a user.
     *
     * @param  Request  $request
     * @param  User  $friend
     * @return \Illuminate\Http\JsonResponse
     */
    public function blockFriend(Request $request, User $friend)
    {
        // Logic to block a user
        // This usually involves updating the relationship status in the database.
        // For simplicity, assuming 'blocked_at' field in the Friend model.
        $authenticatedUser = $request->user();

        $friendship = Friend::updateOrCreate(
            ['user_id' => $authenticatedUser->id, 'friend_id' => $friend->id],
            ['blocked_at' => now()]
        );

        return response()->json(['message' => 'User blocked']);
    }

    /**
     * Unblock a user.
     *
     * @param  Request  $request
     * @param  User  $friend
     * @return \Illuminate\Http\JsonResponse
     */
    public function unblockFriend(Request $request, User $friend)
    {
        $authenticatedUser = $request->user();

        $friendship = Friend::where('user_id', $authenticatedUser->id)
                            ->where('friend_id', $friend->id)
                            ->first();

        if ($friendship) {
            $friendship->update(['blocked_at' => null]);
            return response()->json(['message' => 'User unblocked']);
        } else {
            return response()->json(['error' => 'User not found in friend list'], 400);
        }
    }

    /**
     * Get mutual friends between authenticated user and a specific user.
     *
     * @param  Request  $request
     * @param  User  $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function mutualFriends(Request $request, User $user)
    {
        $authenticatedUser = $request->user();

        // Logic to fetch mutual friends between authenticatedUser and $user
        // Example implementation
        $mutualFriends = $authenticatedUser->friends()->whereHas('friends', function ($query) use ($user) {
            $query->where('friend_id', $user->id);
        })->get();

        return response()->json($mutualFriends);
    }

    /**
     * Get friend suggestions for the authenticated user.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function friendSuggestions(Request $request)
    {
        $authenticatedUser = $request->user();

        // For simplicity, this could be users who are not yet friends with the authenticated user
        $suggestions = User::where('id', '!=', $authenticatedUser->id)
                           ->whereDoesntHave('friends', function ($query) use ($authenticatedUser) {
                               $query->where('friend_id', $authenticatedUser->id);
                           })
                           ->get();

        return response()->json(['suggestions' => $suggestions]);
    }
}
