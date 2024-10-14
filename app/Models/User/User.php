<?php

namespace App\Models\User;

use App\Models\Group\Group;
use App\Models\Page\Page;
use App\Models\Page\PagePostInteraction;
use App\Models\Post\Comments;
use App\Models\Post\Interaction;
use App\Models\Post\InteractionComment;
use App\Models\Post\Posts;
use App\Models\Post\ReplyComment;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject,MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $table='users';
    protected $fillable = [
        'FirstName',
        'LastName',
        'PhoneNumber',
        'email',
        'password',
        'recovery_code'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */

    protected $hidden = [
        'password',
        'remember_token',
        'recovery_code',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
    protected $dates = [
        'email_verified_at',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */

    public function getJWTCustomClaims()
    {
        return [];
    }
    public function getMainImage()
    {
        return $this->hasMany('App\Models\User\MainPhoto', 'user_id', 'id');
    }

    public function mainImage()
    {
        return $this->hasOne(MainPhoto::class)->latest();
    }

    public function getCoverImage()
    {
        return $this->hasMany('App\Models\User\CoverPhoto', 'user_id', 'id');
    }
    public function getallrequsert()
    {
        return $this->hasMany('App\Models\Friends\friendship_requests', 'sender_user_id', 'id');
    }
    public function getallrequsertR()
    {
        return $this->hasMany('App\Models\Friends\friendship_requests', 'receiver_user_id', 'id');
    }
    public function getallfriend()
    {
        return $this->hasMany('App\Models\Friends\friendships', 'sender_user_id', 'id');
    }
    public function posts()
    {
        return $this->hasMany(Posts::class);
    }

    public function stories()
    {
        return $this->hasMany(Stories::class);
    }

    public function profile()
    {
        return $this->hasMany(UserProfile::class);
    }

    public function interactions()
    {
        return $this->hasMany(Interaction::class);
    }

    public function comments()
    {
        return $this->hasMany(Comments::class);
    }

    public function commentInteractions()
    {
        return $this->hasMany(InteractionComment::class);
    }

    public function commentReplies()
    {
        return $this->hasMany(ReplyComment::class);
    }

    public function savedPosts()
    {
        return $this->hasMany(SavedPost::class);
    }

    public function blockedUsers()
    {
        return $this->hasMany(UserBlock::class, 'blocker_id');
    }

    public function friends()
    {
        return $this->belongsToMany(User::class, 'friendships', 'user_id', 'friend_id');
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_user');
    }

    public function ownedGroups()
    {
        return $this->hasMany(Group::class, 'owner_id');
    }

    public function followedPages()
    {
        return $this->belongsToMany(Page::class, 'page_followers', 'user_id', 'page_id');
    }

    public function likedPages()
    {
        return $this->belongsToMany(Page::class, 'page_likes');
    }

    public function interactionsPage()
    {
        return $this->hasMany(PagePostInteraction::class);
    }


}
