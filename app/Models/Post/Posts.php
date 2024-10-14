<?php

namespace App\Models\Post;

use App\Models\User\SavedPost;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Posts extends Model
{
    use HasFactory;

    protected $table='posts';
    protected $fillable = [
        'user_id',
        'Text',
        'files',
    ];
    protected $hidden = [
        'updated_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function comments()
    {
        return $this->hasMany(Comments::class, 'post_id');
    }

    public function interactions()
    {
        return $this->hasMany(Interaction::class, 'post_id');
    }

    public function savedByUsers()
    {
        return $this->hasMany(SavedPost::class);
    }
}
