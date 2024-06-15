<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'type', 'content', 'post_id', 'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public static function list(){
        return self::all();
    }

   
    public static function store($request, $id = null)
    {
        $data = $request->only('type', 'content', 'post_id', 'user_id');

        // Check if the content is an image file
        if ($request->hasFile('content') && $request->file('content')->isValid()) {
            $data['content'] = $request->file('content')->store('public/comments');
        }

        $comment = self::updateOrCreate(['id' => $id], $data);
        return $comment;
    }

}
