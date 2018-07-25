<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class EventJoinRequest extends Model {

    protected $table='event_join_requests';
	
    protected $fillable = ['user_id','event_id','is_accepted','is_accepted_update'];

    protected $dates = [];

    public static $rules = [
        // Validation rules
    ];
    public $timestamps = false;

    // Relationships

}
