<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class OfferOfferCategory extends Model {

    protected $table='offer_offer_categories';
	
    protected $fillable = ['offer_id','offer_category_id'];

    protected $dates = [];

    public static $rules = [
        // Validation rules
    ];
    public $timestamps = false;

    // Relationships

}
