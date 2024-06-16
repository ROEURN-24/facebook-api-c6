<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Friend extends Model
{
    protected $fillable = [
        'user_id', 'friend_id', 'accepted_at'
    ];

    /**
     * Get the user who initiated the friend relationship.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who is the friend.
     */
    public function friend()
    {
        return $this->belongsTo(User::class, 'friend_id');
    }
}
