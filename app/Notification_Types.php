<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Notification_Types extends Model {
	protected $table='notification_types';
    protected $fillable = [];

    protected $dates = [];

    public static $rules = [
        // Validation rules
    ];

    // Relationships
    public function notification()
    {
    	 return $this->hasMany('App\Notification','notification_type_id');
    }

}
