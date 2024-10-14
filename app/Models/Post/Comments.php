<?php

namespace App\Models\Post;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comments extends Model
{
    use HasFactory;
    protected $fillable = [
        'post_id',
        'user_id',
        'content',
        'count_of_Comment'
    ];

    protected $hidden = [
        'updated_at',
    ];

    public function post()
    {
        return $this->belongsTo(Posts::class, 'post_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function replies()
    {
        return $this->hasMany(ReplyComment::class,'comment_id');
    }

    public function interactionComment()
    {
        return $this->hasMany(InteractionComment::class,'comment_id');
    }


}
