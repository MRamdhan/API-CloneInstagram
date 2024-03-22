<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostAttechments extends Model
{
    use HasFactory;
    public $timestamps = false;

    function post() {
        return $this->belongsTo(Post::class);
    }
}
