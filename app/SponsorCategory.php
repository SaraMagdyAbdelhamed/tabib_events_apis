<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class SponsorCategory extends Model {

    protected $table='sponsor_categories';
	
    protected $fillable = ['name','image','created_by','updated_by'];

    protected $dates = [];

    public static $rules = [
        // Validation rules
    ];
    public $timestamps = true;


    public function getImageAttribute($value){
        $base_url = ENV('FOLDER');
        $photo = ($value =='' || is_null($value)) ? '':$base_url.$value;
        return $photo;
    }
  

    // Relationships
    public function sponsors()
    {
        return $this->belongsToMany('App\User', 'user_sponsor_categories', 'sponsor_category_id', 'user_id');
    }

}
