<?php

namespace App\Models\Friends;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class friendship_requests extends Model
{
    use HasFactory;
    protected $table = 'friendship_requests';

    protected $fillable = [
        'sender_user_id',
        'receiver_user_id',
        'status',
    ];

    protected $hidden = [
        'updated_at',
        'id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }

    public function friend()
    {
        return $this->belongsTo(User::class, 'receiver_user_id');
    }
}
