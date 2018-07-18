<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Libraries\Helpers; 

class Offer extends Model {

    protected $primaryKey = 'id';
    protected $table = 'offers';
    protected $fillable = ['name','description','image_en','image_ar','is_active','created_by','updated_by'];
    public $dates = ['created_at','updated_at'];
    
    public function getImageEnAttribute($value){
        $base_url = 'http://eventakom.com/eventakom_dev/public/';
        $photo = ($value =='' || is_null($value)) ? '':$base_url.$value;
        return $photo;
    }
    public function getImageArAttribute($value){
        $base_url = 'http://eventakom.com/eventakom_dev/public/';
        $photo = ($value =='' || is_null($value)) ? '':$base_url.$value;
        return $photo;
    }


    public function scopeIsActive($query){
        return $query->where("is_active",'1');
    }
    public function ScopeWithPaginate($query,$page,$limit){
        return $query->skip(($page-1)*$limit)->take($limit);
    }


    public function getNameAttribute($value)
    {
        $result = (app('translator')->getLocale()=='en') ? Helpers::localization('offers','name',$this->id,1) : Helpers::localization('offers','name',$this->id,2);
        return ($result=='Error')? $value : $result;
    }

    public function getImageAttribute($value){
        
            $base_url = 'http://eventakom.com/eventakom_dev/public/';
            $photo =($value =='' || is_null($value)) ? '':$base_url.$value;
            return $photo;
    }


    //Relations
    public function categories()
    {
    return $this->belongsToMany('App\OfferCategory', 'offer_offer_categories', 'offer_id', 'offer_category_id');
    }

    public function requests()
    {
    return $this->hasMany('App\OfferRequest','offer_id');
    }
}
