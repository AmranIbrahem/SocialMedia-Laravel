<?php

namespace App\Models\Page;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PageReplyComment extends Model
{
    use HasFactory;

    protected $table='page_replies_comments';

    protected $fillable = [
        'comment_id',
        'user_id',
        'reply'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comment()
    {
        return $this->belongsTo(PagePostComment::class, 'comment_id');
    }
}
