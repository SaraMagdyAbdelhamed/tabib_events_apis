<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model {
protected $table='notifications';
    protected $fillable = ['msg','user_id','entity_id','item_id','notification_type_id','is_read','is_sent','is_push','created_at'];

    protected $dates = ['created_at', 'updated_at','schedule'];

    public static $rules = [
        // Validation rules
    ];

    public $timestamps=true;
    // Relationships
    public function notification_type()
    {
    	 return $this->belongsTo('App\Notification_Types','notification_type_id');
    }
    public function notification_push()
    {
    	 return $this->belongsTo('App\Notification_Push','notification_id');
    }
    public function notification_item()
    {
    	 return $this->belongsTo('App\Notification_Item','notification_id');
    }

}
