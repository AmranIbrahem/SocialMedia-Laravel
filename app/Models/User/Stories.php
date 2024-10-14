<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class Stories extends Model
{
    protected $table='stories';

    protected $fillable = [
        'user_id',
        'file_path',
        'file_type',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
