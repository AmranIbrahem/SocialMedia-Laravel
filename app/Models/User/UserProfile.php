<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    use HasFactory;

    protected $table='user_profiles';

    protected $fillable = [
        'user_id',
        'current_location',
        'hometown',
        'marital_status',
        'education',
        'social_accounts',
        'followers_count',
    ];

    protected $hidden = [

    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
