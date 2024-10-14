<?php

namespace App\Models\Group;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupPost extends Model
{
    use HasFactory;

    protected $table = 'group_posts';

    protected $fillable = [
        'group_id',
        'user_id',
        'text',
        'files',
        'count_of_comment',
        'count_of_interaction',
    ];

    protected $casts = [
        'files' => 'array',
    ];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->hasMany(Group_Post_Comment::class, 'post_id');
    }


    public function interactions()
    {
        return $this->hasMany(GroupPostInteractions::class, 'post_id');
    }



}
