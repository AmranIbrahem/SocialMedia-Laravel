<?php

namespace App\Models\Page;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasFactory;

    protected $table='pages';

    protected $fillable = [
        'name',
        'bio',
        'likes_count',
        'followers_count',
        'owner_id',
        'admins'
    ];

    protected $casts = [
        'admins' => 'array',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function followers()
    {
        return $this->belongsToMany(User::class, 'page_followers', 'page_id', 'user_id');
    }
    public function likes()
    {
        return $this->belongsToMany(User::class, 'page_likes', 'page_id', 'user_id');
    }

    public function posts()
    {
        return $this->hasMany(PagePost::class, 'page_id');
    }
}
