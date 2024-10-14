<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MainPhoto extends Model
{
    use HasFactory;
    protected $table='main_photos';
    protected $fillable = [
        'user_id',
        'Main_Image',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */

    protected $hidden = [
        'updated_at',
        'user_id',
        'id'
    ];


}
