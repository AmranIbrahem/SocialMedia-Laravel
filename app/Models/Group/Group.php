<?php

namespace App\Models\Group;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;
    protected $table = 'groups';

    protected $fillable = [
        'name',
        'description',
        'owner_id',
        'admins',
        'members_count',

    ];


    public function users()
    {
        return $this->belongsToMany(User::class, 'group_user', 'group_id', 'user_id');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'group_user', 'group_id', 'user_id');
    }

    public function posts()
    {
        return $this->hasMany(GroupPost::class);
    }

    public function setAdminsAttribute($value)
    {
        $this->attributes['admins'] = json_encode($value);
    }

    public function getAdminsAttribute($value)
    {
        return json_decode($value, true);
    }
}
