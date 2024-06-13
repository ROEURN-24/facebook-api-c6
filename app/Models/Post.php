<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use HasFactory ,SoftDeletes;

    protected $fillable = [
        'image_url',
        'description',
        'user_id',
    ];
    public static function list(){
        return self::all();
    }
}
