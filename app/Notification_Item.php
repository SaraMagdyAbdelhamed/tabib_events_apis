<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Notification_Item extends Model {
	protected $table='notification_items';
    protected $fillable = [];

    protected $dates = [];

    public static $rules = [
        // Validation rules
    ];

    // Relationships
    public function notification()
    {
    	 return $this->hasMany('App\Notification','notification_id');
    }

}
