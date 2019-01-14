<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model {

    protected $fillable = [];

    protected $dates = [];

    public static $rules = [
        // Validation rules
    ];

    // Relationships
    public function events()
    {
        return $this->belongsToMany('App\Event','event_categories');
    }
    //Attributes

     public function getImageAttribute($value)
    {
        $base_url = ENV('FOLDER');
        $photo = $base_url.$value;
        return $photo;
    }

}
