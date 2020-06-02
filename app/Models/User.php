<?php

namespace App\Models;

use Auth;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;


class User extends Authenticatable
{
    use HasApiTokens, Notifiable;
    public $timestamps = false;

	protected $fillable = [
        'username', 'user_id',  'password'
    ];
    protected $hidden = [
        'password', 'remember_token',
    ];

}