<?php

namespace App\Models\Post;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InteractionComment extends Model
{
    use HasFactory;

    protected $table='interaction_comment';

    protected $fillable = [
        'comment_id',
        'user_id',
        'type',
    ];

    protected $hidden = [
        'updated_at',
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
