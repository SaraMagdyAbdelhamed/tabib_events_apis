<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class OfferRequest extends Model {

    protected $table='offer_requests';
	
    protected $fillable = ['user_id','offer_id','is_accepted'];

    protected $dates = [];

    public static $rules = [
        // Validation rules
    ];
    public $timestamps = true;

    // Relationships
    
    public function offers()
    {
    return $this->belongsToMany('App\Offer');
    }

    public function users()
    {
    return $this->belongsToMany('App\User');
    }

}
