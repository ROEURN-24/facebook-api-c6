<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    // public function toArray(Request $request): array
    // {
    //     $likeCounts = $this->likes->groupBy('type')->map(function ($likes, $type) {
    //         return [
    //             'type' => $type,
    //             'count' => $likes->count(),
    //         ];
    //     })->values()->toArray();

    //     return [
    //         "id" => $this->id,
    //         "title" => $this->title,
    //         "image" => $this->image,
    //         'like-number' => $this->likes->count(),
    //         'like_types' => $likeCounts,
    //     ];
    // }

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

        return [
            "id" => $this->id,
            "title" => $this->title,
            "image" => $this->image,
            'like-number' => $this->likes->count(),
            'like_types' => $likeCounts,
        ];
    }
    
}
