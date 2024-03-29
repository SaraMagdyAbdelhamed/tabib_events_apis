<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class userGoing extends Model {

    protected $table='user_going';
    protected $fillable = ['user_id','event_id','is_accepted'];

    protected $dates = [];
    public $timestamps = false;
    public static $rules = [
        // Validation rules
    ];

    // Relationships
    public function users()
    {
        return $this->belongsTo('App\User','user_id');
    }

     public function event()
    {
        return $this->belongsTo('App\Event','event_id');
    }

}
