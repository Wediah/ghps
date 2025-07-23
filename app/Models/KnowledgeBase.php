<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KnowledgeBase extends Model
{
    protected $fillable = [
        'title', 'content', 'category', 'summary',
        'is_published', 'author_id', 'views'
    ];

    protected $casts = [
        'is_published' => 'boolean'
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
