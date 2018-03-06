<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Comment extends Model
{
    protected $fillable = [
        'author_name', 'author_email', 'author_url', 'body', 'post_id'
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function getDateAttribute()
    {
        return $this->created_at->diffForHumans();
    }
}
