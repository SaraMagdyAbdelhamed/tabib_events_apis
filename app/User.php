<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username', 'email','password','first_name','last_name','tele_code','mobile','country_id','city_id',
        'gender_id','photo','birthdate','is_active','is_mobile_verified','is_email_verified','created_by',
        'updated_by','created_at','updated_at','device_token','mobile_os','is_social','api_token','is_valid_token',
        'social_token','lang_id','mobile_verification_code','is_mobile_verification_code_expired','email_verification_code',
        'verification_date','verification_count','longitude','latitude','timezone','remember_token'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];
}
