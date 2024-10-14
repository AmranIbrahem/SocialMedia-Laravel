<?php

namespace App\Models\Post;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReplyComment extends Model
{
    use HasFactory;
    protected $table='replies_comment';

    protected $fillable = [
        'comment_id',
        'user_id',
        'reply',
    ];

    public function comment()
    {
        return $this->belongsTo(Comments::class,'comment_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
