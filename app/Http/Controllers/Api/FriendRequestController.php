<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Friend;
use App\Models\FriendRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FriendRequestController extends Controller
{
 /**
     * Send a friend request to a user.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function send(Request $request)
    {
        try {
            // Validate the incoming request data
            $request->validate([
                'recipient_id' => 'required|exists:users,id',
                'message' => 'nullable|string',
            ]);

            // Retrieve recipient ID from request
            $recipientId = $request->input('recipient_id');

            // Check if a pending request already exists
            $existingRequest = FriendRequest::where('sender_id', $request->user()->id)
                ->where('recipient_id', $recipientId)
                ->where('status', 'pending')
                ->first();

            if ($existingRequest) {
                return response()->json(['error' => 'Friend request already sent'], 400);
            }

            // Create a new friend request
            $friendRequest = FriendRequest::create([
                'sender_id' => $request->user()->id,
                'recipient_id' => $recipientId,
                'message' => $request->input('message'),
                'status' => 'pending',
            ]);

            return response()->json(['message' => 'Friend request sent']);
        } catch (\Exception $e) {
            Log::error('Failed to send friend request: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to send friend request. Please try again later.'], 500);
        }
    }



    /**
     * Accept a friend request.
     *
     * @param  FriendRequest  $friendRequest
     * @return \Illuminate\Http\JsonResponse
     */
    public function accept(FriendRequest $friendRequest)
    {
        // Ensure the authenticated user is the recipient of the friend request
        if (auth()->id() !== $friendRequest->recipient_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        DB::beginTransaction();

        try {
            // Update the friend request status to accepted
            $friendRequest->update(['status' => 'accepted', 'accepted_at' => now()]);

            // Create friend entries for both users
            Friend::create([
                'user_id' => $friendRequest->sender_id,
                'friend_id' => $friendRequest->recipient_id,
                'accepted_at' => now(),
            ]);

            Friend::create([
                'user_id' => $friendRequest->recipient_id,
                'friend_id' => $friendRequest->sender_id,
                'accepted_at' => now(),
            ]);

            DB::commit();

            return response()->json(['message' => 'Friend request accepted']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to accept friend request: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to accept friend request. Please try again later.'], 500);
        }
    }

    /**
     * Decline a friend request.
     *
     * @param  FriendRequest  $friendRequest
     * @return \Illuminate\Http\JsonResponse
     */
    public function decline(FriendRequest $friendRequest)
    {
        try {
            // Update the friend request status to declined
            $friendRequest->update(['status' => 'declined']);

            return response()->json(['message' => 'Friend request declined']);
        } catch (\Exception $e) {
            Log::error('Failed to decline friend request: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to decline friend request. Please try again later.'], 500);
        }
    }

    /**
     * Cancel a friend request sent by the authenticated user.
     *
     * @param  FriendRequest  $friendRequest
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancelRequest(FriendRequest $friendRequest)
    {
        try {
            if ($friendRequest->status === 'pending' && $friendRequest->sender_id === auth()->id()) {
                $friendRequest->delete();
                return response()->json(['message' => 'Friend request canceled']);
            }

            return response()->json(['error' => 'Unable to cancel friend request'], 400);
        } catch (\Exception $e) {
            Log::error('Failed to cancel friend request: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to cancel friend request. Please try again later.'], 500);
        }
    }
}
