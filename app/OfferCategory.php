<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class OfferCategory extends Model {

    protected $table='offer_categories';
	
    protected $fillable = ['name','created_by','updated_by'];

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
}
