<?php

    namespace App\Models\Post;

    use App\Models\User\User;
    use Illuminate\Database\Eloquent\Model;

    class Interaction extends Model
    {
        protected $fillable = [
            'user_id',
            'post_id',
            'type',
        ];

        public function user()
        {
            return $this->belongsTo(User::class);
        }

        public function post()
        {
            return $this->belongsTo(Posts::class, 'post_id');
        }

    }
