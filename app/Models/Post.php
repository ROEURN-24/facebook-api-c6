<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'image',
        'user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public static function list()
    {
        return self::all();
    }

    public static function store($request, $id = null)
    {
        $data = $request->only('title', 'image');
        $data['user_id'] = $request->user_id ?? auth()->id(); 

        if ($request->hasFile('image')) {

            $image = $request->file('image');
            $path = $image->store('posts', 'public');
            $path = Storage::url($path);
            $data['image'] = $path;
        }

        if ($id) {
            $post = self::find($id);
            if ($post && !$request->hasFile('image')) {
                $data['image'] = $post->image; 
            }
        }

        $post = self::updateOrCreate(['id' => $id], $data);
        return $post;
    }
    
}
