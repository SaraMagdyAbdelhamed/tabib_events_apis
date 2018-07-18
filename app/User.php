<?php

namespace App;
use Laravel\Passport\HasApiTokens;
use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use carbon\carbon;
use App\Libraries\Helpers;
class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use HasApiTokens,Authenticatable, Authorizable;

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

    public static $rules = 
    [ 
    'first_name' => 'between:1,100',
    'last_name' => 'between:1,12',
    'email' => 'email|unique:users|max:35',
    // 'city_id' => 'required',
    'mobile' => 'required|numeric|unique:users',
    'tele_code'=>'required',
    'password' => 'required|between:8,20',
    'mobile_os' => 'in:android,ios',
    'lang_id' => 'in:1,2',
    'country_id'=>'required',
    'city_id'=>'required',
    'gender_id'=>'required'
    ];

        // Relationships
    public function rules()
    {
    return $this->belongsToMany('App\Rule', 'user_rules', 'user_id', 'rule_id');
    }
    public function sponsors_categories()
    {
    return $this->belongsToMany('App\SponsorCategory', 'user_sponsor_categories', 'user_id', 'sponsor_category_id');
    }
    public function offers_requests()
    {
    return $this->hasMany('App\OfferRequest','user_id');
    }
    public function user_info()
    {
        return $this->hasOne('App\userInfo','user_id');
    }
    public function user_going()
    {
        return $this->hasMany('App\userGoing','user_id');
    }
    
    public function getPhotoAttribute($value)
    {
        $base_url = 'http://eventakom.com/eventakom_dev/public/';
        $photo = $base_url.$value;
        return $photo;
    }


     public function setBirthDateAttribute($value)
    {
        if(Helpers::isValidTimestamp($value))
        {
        $this->attributes['birthdate'] = gmdate("Y-m-d\TH:i:s\Z",$value);
        }else{

          return Helpers::Get_Response(403, 'error', trans('Invalid date format'), [], []);   
        }
    }
}
