<?php

namespace App\Models\Page;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PagePost extends Model
{
    use HasFactory;

    protected $table='page_posts';

    protected $fillable = [
        'page_id',
        'Text',
        'files',
        'count_of_Comment',
        'count_of_Interaction'
    ];

    protected $casts = [
        'files' => 'array',
    ];

    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    public function comments()
    {
        return $this->hasMany(PagePostComment::class, 'page_post_id');
    }

    public function interactions()
    {
        return $this->hasMany(PagePostInteraction::class, 'post_page_id');
    }

}
