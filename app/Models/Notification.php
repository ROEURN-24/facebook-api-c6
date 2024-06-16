<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', // The user to whom the notification belongs
        'type',    // Type of notification (e.g., 'reaction', 'comment', 'share')
        'content', // Notification content or message
        'read_at', // Timestamp when the notification was read
    ];

    /**
     * Get the user that owns the notification
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
