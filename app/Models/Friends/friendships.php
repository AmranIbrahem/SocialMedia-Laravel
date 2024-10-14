<?php

namespace App\Models\Friends;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class friendships extends Model
{
    use HasFactory;
    protected $table = 'friendships';

    protected $fillable = [
        'sender_user_id',
        'receiver_user_id',
    ];
    protected $hidden = [
        'updated_at',
        'id'
    ];

}
