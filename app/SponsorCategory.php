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
        $base_url = 'http://eventakom.com/eventakom_dev/public/';
        $photo = ($value =='' || is_null($value)) ? '':$base_url.$value;
        return $photo;
    }
  

    // Relationships
    public function users()
    {
        return $this->belongsToMany('App\User');
    }

}
