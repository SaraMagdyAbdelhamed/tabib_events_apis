<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class userInfo extends Model {

    protected $table='users_info';
    protected $fillable = ['user_id','mobile2','mobile3','region_id','address','is_backend','is_profile_completed'];


    protected $dates = [];
    public $timestamps = false;
    public static $rules = [
        // Validation rules
        'region_id'=>'required'
    ];


    // Relationships
    public function users()
    {
        return $this->belongsTo('App\User','user_id');
    }


}
