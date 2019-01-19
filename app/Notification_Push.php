<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Notification_Push extends Model {
	protected $table='notifications_push';
    protected $fillable = ['notification_id','device_token','mobile_os','lang_id','user_id'];

    protected $dates = [];
    
    public $timestamps=false;
    public static $rules = [
        // Validation rules
    ];

    // Relationships
     public function notification()
    {
    	 return $this->hasMany('App\Notification','notification_id');
    }

}
