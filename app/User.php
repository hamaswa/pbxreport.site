<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Permissions\HasPermissionsTrait;


class User extends Authenticatable
{
    use Notifiable, HasPermissionsTrait;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password','did_no','mobile_no','active'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function extensions(){
        return $this->hasMany('App\Extension','user_id');
    }

    public function queues(){
        return $this->hasMany('App\Queue','user_id');
    }


}
