<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Libraries\Helpers;
class GeoRegion extends Model {

    protected $fillable = [];

    public function geo_city()
    {
        return $this->belongsTo("App\GeoCity","city_id"); //important dont forget to add (ger_county_id) to geo_cities table
    }

    //Localizations

    public function getNameAttribute($value)
    {
        $result = (app('translator')->getLocale()=='en') ? Helpers::localization('geo_regions','name',$this->id,1) : Helpers::localization('geo_regions','name',$this->id,2);
        return ($result=='Error')? $value : $result;
    }

    public static function region_entity_ar(){
      return static::query()->join('entity_localizations','geo_regions.id','=','entity_localizations.item_id')
      ->where('entity_id','=',7)->where('field','=','name')
      ->select('geo_regions.*','entity_localizations.value');
           

    }

}
