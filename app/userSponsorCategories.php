<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class userSponsorCategories extends Model {

    protected $table='user_sponsor_categories';
	
    protected $fillable = ['user_id','sponsor_category_id'];

    protected $dates = [];

    public static $rules = [
        // Validation rules
    ];
    public $timestamps = false;

}
