<?php

namespace App\Models\Page;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PageLikes extends Model
{
    use HasFactory;


    protected $table='page_likes';

    protected $fillable = [
        'page_id',
        'user_id',
    ];

    public function page()
    {
        return $this->belongsTo(Page::class, 'page_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
