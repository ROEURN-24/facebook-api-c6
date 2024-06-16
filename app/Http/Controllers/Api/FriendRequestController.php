<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FriendRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $request->validate([
            'recipient_id' => 'required|exists:users,id',
            'message' => 'nullable|string',
        ]);

        $recipientId = $request->recipient_id;

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
    }

    /**
     * Accept a friend request.
     *
     * @param  Request  $request
     * @param  FriendRequest  $friendRequest
     * @return \Illuminate\Http\JsonResponse
     */
    public function accept(Request $request, FriendRequest $friendRequest)
    {
        // Ensure the authenticated user is the recipient of the friend request
        if ($request->user()->id !== $friendRequest->recipient_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        DB::beginTransaction();

        try {
            // Update the friend request status to accepted
            $friendRequest->update(['status' => 'accepted', 'accepted_at' => now()]);

            // Create a new friend entry for both users
            $friendRequest->sender()->first()->friends()->attach($friendRequest->recipient_id);
            $friendRequest->recipient()->first()->friends()->attach($friendRequest->sender_id);

            DB::commit();

            return response()->json(['message' => 'Friend request accepted']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to accept friend request'], 500);
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
        $friendRequest->update(['status' => 'declined']);

        return response()->json(['message' => 'Friend request declined']);
    }

    /**
     * Get pending friend requests for the authenticated user.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function pendingRequests(Request $request)
    {
        $pendingRequests = $request->user()->receivedFriendRequests()
            ->where('status', 'pending')
            ->with('sender')
            ->get();

        return response()->json($pendingRequests);
    }

    /**
     * Get sent friend requests by the authenticated user.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sentRequests(Request $request)
    {
        $sentRequests = $request->user()->sentFriendRequests()
            ->with('recipient')
            ->get();

        return response()->json($sentRequests);
    }

    /**
     * Cancel a friend request sent by the authenticated user.
     *
     * @param  FriendRequest  $friendRequest
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancelRequest(FriendRequest $friendRequest)
    {
        if ($friendRequest->status === 'pending' && $friendRequest->sender_id === auth()->id()) {
            $friendRequest->delete();
            return response()->json(['message' => 'Friend request canceled']);
        }

        return response()->json(['error' => 'Unable to cancel friend request'], 400);
    }
}
