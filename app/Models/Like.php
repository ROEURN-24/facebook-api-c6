<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    use HasFactory;

    protected $fillable = [
        'like_number',
        'type',
        'image',
        'post_id',
        'user_id',
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function list()
    {
        return self::all();
    }

    public static function store($request)
    {
        $data = $request->only('like_number','type', 'image', 'post_id', 'user_id');

        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $data['image'] = $request->file('image')->store('public/likes');
        }

        $like = self::create($data);
        return $like;
    }
    
}