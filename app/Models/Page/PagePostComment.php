<?php

namespace App\Models\Page;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PagePostComment extends Model
{
    use HasFactory;

    protected $table = 'page_post_comments';

    protected $fillable = [
        'page_post_id',
        'user_id',
        'content',
        'count_of_Reply',
        'count_of_Interaction'
    ];

    public function post()
    {
        return $this->belongsTo(PagePost::class, 'page_post_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
