<?php

namespace App\Models\Page;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PagePostInteraction extends Model
{
    use HasFactory;

    protected $table='page_post_interactions';

    protected $fillable = [
        'user_id',
        'post_page_id',
        'type'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pagePost()
    {
        return $this->belongsTo(PagePost::class, 'post_page_id');
    }
}
