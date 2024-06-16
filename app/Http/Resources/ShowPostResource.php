<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShowPostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */

    public function toArray(Request $request): array
    {
        $likeCounts = $this->likes->groupBy('type')->map(function ($likes, $type) {
            $users = $likes->pluck('user.name')->toArray();
            return [
                'type' => $type,
                'count' => $likes->count(),
                'users' => $users,
            ];
        })->values()->toArray();

        $comments = $this->comments->map(function ($comment) {
            return [
                'id' => $comment->id,
                'content' => $comment->content,
                'user' => $comment->user->name,
                'created_at' => $comment->created_at->toDateTimeString(),
            ];
        })->toArray();

        return [
            "id" => $this->id,
            "title" => $this->title,
            "image" => $this->image,
            'like-number' => $this->likes->count(),
            'like_types' => $likeCounts,
            'comments' => $comments, 
        ];
    }
}
