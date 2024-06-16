<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Story extends Model
{
    protected $fillable = ['user_id', 'content', 'expires_at', 'visibility'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeVisibleTo($query, $user)
    {
        return $query->where(function ($q) use ($user) {
            $q->where('visibility', 'public')
              ->orWhere(function ($q2) use ($user) {
                  $q2->where('visibility', 'friends')
                     ->whereHas('user.friends', function ($q3) use ($user) {
                         $q3->where('friends.friend_id', $user->id);
                     });
              })
              ->orWhere('user_id', $user->id);
        })->where('expires_at', '>', Carbon::now());
    }
}
