<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlogPostTag extends Model
{
    protected $table = 'blog_post_tag';

    protected $fillable = ['blog_post_id', 'tag'];

    public function post(): BelongsTo
    {
        return $this->belongsTo(BlogPost::class);
    }
}
