<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Libraries\Helpers;

class Currency extends Model {

    protected $primaryKey = 'id';
    protected $table = 'currencies';

    protected $fillable = ['name','symbol','rate','def','subdivision_name','sort_order'];
    public $timestamps = false;

    // relations

    // 1 entity belongs to many entity_localizations
    public function price() {
        return $this->hasOne('App\Price');
    }

    //localization
    public function getNameAttribute($value)
    {
        $result = (app('translator')->getLocale()=='en') ? Helpers::localization('currencies','name',$this->id,1) : Helpers::localization('currencies','name',$this->id,2);
        return ($result=='Error')? $value : $result;
    }

    public function ticket(){
        return $this->hasOne('App\EventTicket');   
    }

}
