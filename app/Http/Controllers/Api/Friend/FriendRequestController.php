<?php

namespace App\Http\Controllers\Friend;

use App\Http\Controllers\Controller;
use App\Models\Friend;
use App\Models\FriendRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class FriendRequestController extends Controller
{
    public function send(Request $request)
    {
        $request->validate([
            'recipient_id' => 'required|exists:users,id',
            'message' => 'nullable|string',
        ]);

        $existingRequest = FriendRequest::where('sender_id', $request->user()->id)
            ->where('recipient_id', $request->recipient_id)
            ->where('status', 'pending')
            ->first();

        if ($existingRequest) {
            return response()->json(['error' => 'Friend request already sent'], 400);
        }

        $friendRequest = FriendRequest::create([
            'sender_id' => $request->user()->id,
            'recipient_id' => $request->recipient_id,
            'message' => $request->message,
            'status' => 'pending',
        ]);

        return response()->json(['message' => 'Friend request sent']);
    }

    public function accept(FriendRequest $friendRequest)
    {
        DB::beginTransaction();

        try {
            $friendRequest->update(['status' => 'accepted', 'accepted_at' => now()]);

            Friend::create([
                'user_id' => $friendRequest->sender_id,
                'friend_id' => $friendRequest->recipient_id,
                'accepted_at' => now(),
            ]);

            DB::commit();

            return response()->json(['message' => 'Friend request accepted']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to accept friend request'], 500);
        }
    }

    public function decline(FriendRequest $friendRequest)
    {
        $friendRequest->update(['status' => 'declined']);

        return response()->json(['message' => 'Friend request declined']);
    }

    public function pendingRequests(Request $request)
    {
        $pendingRequests = $request->user()->receivedFriendRequests()
            ->where('status', 'pending')
            ->with('sender')
            ->get();

        return response()->json($pendingRequests);
    }

    public function sentRequests(Request $request)
    {
        $sentRequests = $request->user()->sentFriendRequests()
            ->with('recipient')
            ->get();

        return response()->json($sentRequests);
    }

    public function cancelRequest(FriendRequest $friendRequest)
    {
        if ($friendRequest->status === 'pending' && $friendRequest->sender_id === auth()->id()) {
            $friendRequest->delete();
            return response()->json(['message' => 'Friend request canceled']);
        }

        return response()->json(['error' => 'Unable to cancel friend request'], 400);
    }

    public function deleteFriend(User $friend)
    {
        $authenticatedUser = auth()->user();

        // Check if the authenticated user has this user as a friend
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
}
