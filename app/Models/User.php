<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'uuid';
    }

    /**
     * Define the relationship for sent friend requests.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function sentFriendRequests()
    {
        return $this->hasMany(FriendRequest::class, 'sender_id');
    }

    /**
     * Define the relationship for received friend requests.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function receivedFriendRequests()
    {
        return $this->hasMany(FriendRequest::class, 'recipient_id');
    }

    /**
     * Define the relationship for friends.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function friends()
    {
        return $this->belongsToMany(User::class, 'friends', 'user_id', 'friend_id')
                    ->withPivot('accepted_at')
                    ->withTimestamps();
    }

    /**
     * Check if the user has a specific friend.
     *
     * @param  User  $friend
     * @return bool
     */
    public function hasFriend(User $friend)
    {
        return $this->friends()->where('id', $friend->id)->exists();
    }

    /**
     * Remove a friend from the user's friends list.
     *
     * @param  User  $friend
     * @return void
     */
    public function removeFriend(User $friend)
    {
        $this->friends()->detach($friend->id);
    }

    /**
     * Define the relationship for user posts.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}

