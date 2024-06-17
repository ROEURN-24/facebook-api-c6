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
            $request->validate([
                'recipient_id' => 'required|exists:users,id',
            ]);

            $user = $request->user();

            if (!$user) {
                Log::error('Failed to send friend request: User not authenticated');
                return response()->json(['error' => 'User not authenticated'], 401);
            }

            $recipientId = $request->recipient_id;

            // Check if a pending request already exists
            $existingRequest = FriendRequest::where('sender_id', $user->id)
                ->where('recipient_id', $recipientId)
                ->where('status', 'pending')
                ->first();

            if ($existingRequest) {
                return response()->json(['error' => 'Friend request already sent'], 400);
            }

            // Create a new friend request
            $friendRequest = FriendRequest::create([
                'sender_id' => $user->id,
                'recipient_id' => $recipientId,
                'status' => 'pending',
            ]);

            return response()->json(['message' => 'Friend request sent']);
        } catch (\Exception $e) {
            Log::error('Failed to send friend request: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Failed to send friend request. Please try again later.'], 500);
        }
    }

    /**
     * Accept a friend request.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function accept(Request $request, $id)
    {
        $friendRequest = FriendRequest::findOrFail($id);

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
     * @param  Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function decline(Request $request, $id)
    {
        $friendRequest = FriendRequest::findOrFail($id);

        // Ensure the authenticated user is the recipient of the friend request
        if (auth()->id() !== $friendRequest->recipient_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

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
     * @param  Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancelRequest(Request $request, $id)
    {
        $friendRequest = FriendRequest::findOrFail($id);

        // Ensure the authenticated user is the sender of the friend request
        if (auth()->id() !== $friendRequest->sender_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            if ($friendRequest->status === 'pending') {
                $friendRequest->delete();
                return response()->json(['message' => 'Friend request canceled']);
            }

            return response()->json(['error' => 'Unable to cancel friend request'], 400);
        } catch (\Exception $e) {
            Log::error('Failed to cancel friend request: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to cancel friend request. Please try again later.'], 500);
        }
    }

    /**
     * Get the list of sent friend requests.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sentRequests(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                Log::error('Failed to retrieve sent friend requests: User not authenticated');
                return response()->json(['error' => 'User not authenticated'], 401);
            }

            $sentRequests = FriendRequest::where('sender_id', $user->id)
                ->where('status', 'pending')
                ->with('recipient:id,name,email')
                ->get();

            return response()->json([
                'status' => true,
                'message' => 'Sent friend requests retrieved successfully',
                'data' => $sentRequests
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve sent friend requests: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Failed to retrieve sent friend requests. Please try again later.'], 500);
        }
    }

    /**
     * Get the list of pending friend requests.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function pendingRequests(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                Log::error('Failed to retrieve pending friend requests: User not authenticated');
                return response()->json(['error' => 'User not authenticated'], 401);
            }

            $pendingRequests = FriendRequest::where('recipient_id', $user->id)
                ->where('status', 'pending')
                ->with('sender:id,name,email')
                ->get();

            return response()->json([
                'status' => true,
                'message' => 'Pending friend requests retrieved successfully',
                'pending_requests' => $pendingRequests
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve pending friend requests: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Failed to retrieve pending friend requests. Please try again later.'], 500);
        }
    }
}
