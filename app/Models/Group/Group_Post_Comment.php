<?php

namespace App\Models\Group;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group_Post_Comment extends Model
{
    use HasFactory;
    protected $table = 'group__post__comments';

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
        return $this->belongsTo(GroupPost::class, 'post_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function interactionComment()
    {
        return $this->hasMany(GroupInteractionsComment::class,'comment_id');
    }

    public function replies()
    {
        return $this->hasMany(GroupRepliesComment::class,'comment_id');
    }
}
