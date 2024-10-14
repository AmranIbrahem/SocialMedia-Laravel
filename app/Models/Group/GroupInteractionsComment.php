<?php

namespace App\Models\Group;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupInteractionsComment extends Model
{
    use HasFactory;

    protected $table='group_interactions_comments';

    protected $fillable = [
        'comment_id',
        'user_id',
        'type',
    ];

    protected $hidden = [
        'updated_at',
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
