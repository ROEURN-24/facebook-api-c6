<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use App\Models\FriendRequest;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Story;
use App\Models\Follower;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'remember_token', 'image',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get the comments associated with the user.
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Get the posts associated with the user.
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Get the friend requests sent by the user.
     */
    public function sentFriendRequests()
    {
        return $this->hasMany(FriendRequest::class, 'sender_id');
    }

    /**
     * Get the friend requests received by the user.
     */
    public function receivedFriendRequests()
    {
        return $this->hasMany(FriendRequest::class, 'recipient_id');
    }
    /**
     * Get the friends of the user.
     */
    public function friends()
    {
        return $this->belongsToMany(User::class, 'friends', 'user_id', 'friend_id')
            ->withPivot('accepted_at')
            ->withTimestamps();
    }

    /**
     * Check if a user is a friend of the authenticated user.
     *
     * @param User $user
     * @return bool
     */
    public function isFriend(User $user)
    {
        return $this->friends()->where('friend_id', $user->id)->exists();
    }

    /**
     * Get the followers of the user.
     */
    public function followers()
    {
        return $this->hasMany(Follower::class, 'user_id');
    }

    /**
     * Get the users following the user.
     */
    public function following()
    {
        return $this->hasMany(Follower::class, 'follower_id');
    }

    /**
     * Check if the user is following another user.
     *
     * @param int $userId
     * @return bool
     */
    public function isFollowing($userId)
    {
        return $this->following()->where('user_id', $userId)->exists();
    }

    /**
     * Check if the user is followed by another user.
     *
     * @param int $userId
     * @return bool
     */
    public function isFollowedBy($userId)
    {
        return $this->followers()->where('follower_id', $userId)->exists();
    }

    /**
     * Get the stories posted by the user.
     */
    public function stories()
    {
        return $this->hasMany(Story::class);
    }
}
