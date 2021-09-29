<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'salutation','first_name','last_name', 'email','image','gender','phone_number','country','state','city', 'password','cover_image','self_intro','website','address',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    public function notipref(){
        return $this->hasMany('App\NotificationPreferece','user_id','id');
    }
    public function review(){
        return $this->hasMany('App\Review','user_id','id');
    }
    // public function rating(){
    //     return $this->hasMany('App\Rating','user_id','id');
    // }
   
    public function follow(){
        return $this->hasMany('App\Follow','user_id','id');
    }

    public function bookmark(){
        return $this->hasMany('App\Bookmark','user_id','id')->orderBy('id','desc');
    }
    public function recentviewed()
    {
        return $this->hasMany('App\RecentViewed','user_id','id')->orderBy('id','desc');
    }
   
    public function country_r(){
        return $this->belongsTo('App\Country','country','id');
    }

    public function state_r(){
         return $this->belongsTo('App\State','state','id');
    }

    public function city_r(){
         return $this->belongsTo('App\City','city','id');
    }

     public function isAdmin()
    {
        return ($this->role_id == 1);
    }
     public function isUser()
    {
        return ($this->role_id == 0);
    }
}
