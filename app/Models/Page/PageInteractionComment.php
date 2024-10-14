<?php

namespace App\Models\Page;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PageInteractionComment extends Model
{
    use HasFactory;

    protected $table='page_interaction_comments';

    protected $fillable = [
        'user_id',
        'comment_id',
        'type'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comment()
    {
        return $this->belongsTo(PagePostComment::class, 'comment_id');
    }
}
