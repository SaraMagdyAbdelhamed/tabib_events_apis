<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Libraries\Helpers;
class GeoCity extends Model {

    protected $hidden = [];
    // Relationships
    public function geo_country()
    {
        return $this->belongsTo("App\GeoCountry","country_id"); //important dont forget to add (ger_county_id) to geo_cities table
    }

    //Localizations

    public function getNameAttribute($value)
    {
        $result = (app('translator')->getLocale()=='en') ? Helpers::localization('geo_cities','name',$this->id,1) : Helpers::localization('geo_cities','name',$this->id,2);
        return ($result=='Error')? $value : $result;
    }

    public static function city_entity_ar(){
      return static::query()->join('entity_localizations','geo_cities.id','=','entity_localizations.item_id')
      ->where('entity_id','=',7)->where('field','=','name')
      ->select('geo_cities.*','entity_localizations.value');
           

    }

}
