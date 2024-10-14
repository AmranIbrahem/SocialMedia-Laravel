<?php

namespace App\Models\Group;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupPostInteractions extends Model
{
    use HasFactory;

    protected $table = 'group_post_interactions';

    protected $fillable = [
        'user_id',
        'post_id',
        'type',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function post()
    {
        return $this->belongsTo(GroupPost::class, 'post_id');
    }
}
