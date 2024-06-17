<?php

namespace App\Services;

use App\Models\FriendRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class FriendshipService
{
    public function sendFriendRequest($senderId, $recipientId, $message)
    {
        // Check if a pending request already exists
        $existingRequest = FriendRequest::where('sender_id', $senderId)
            ->where('recipient_id', $recipientId)
            ->where('status', 'pending')
            ->first();

        if ($existingRequest) {
            throw new \Exception('Friend request already sent');
        }

        // Create a new friend request
        FriendRequest::create([
            'sender_id' => $senderId,
            'recipient_id' => $recipientId,
            'message' => $message,
            'status' => 'pending',
        ]);
    }

    public function acceptFriendRequest(FriendRequest $friendRequest)
    {
        DB::beginTransaction();

        try {
            $friendRequest->update(['status' => 'accepted', 'accepted_at' => now()]);

            // Assuming there is a Friend model or pivot table to manage friendships
            User::find($friendRequest->sender_id)->friends()->attach($friendRequest->recipient_id);
            User::find($friendRequest->recipient_id)->friends()->attach($friendRequest->sender_id);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Failed to accept friend request');
        }
    }

    public function declineFriendRequest(FriendRequest $friendRequest)
    {
        $friendRequest->update(['status' => 'declined']);
    }

    public function removeFriend(User $user, User $friend)
    {
        $user->friends()->detach($friend->id);
        $friend->friends()->detach($user->id);
    }

    public function hasFriend(User $user, User $friend)
    {
        return $user->friends()->where('friend_id', $friend->id)->exists();
    }
}
