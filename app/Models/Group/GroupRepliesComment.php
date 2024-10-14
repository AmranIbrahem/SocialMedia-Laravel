<?php

namespace App\Models\Group;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupRepliesComment extends Model
{
    use HasFactory;

    protected $table='group_replies_comments';

    protected $fillable = [
        'comment_id',
        'user_id',
        'reply',
    ];

    public function comment()
    {
        return $this->belongsTo(Group_Post_Comment::class,'comment_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }


}
