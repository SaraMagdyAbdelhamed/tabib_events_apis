<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Libraries\Helpers;

class Gender extends Model {

    protected $primaryKey = 'id';
    protected $table = 'genders';

    protected $fillable = ['name'];
    public $timestamps = false;


    public function getNameAttribute($value)
    {
        $result = (app('translator')->getLocale()=='en') ? Helpers::localization('genders','name',$this->id,1) : Helpers::localization('genders','name',$this->id,2);
        return ($result=='Error')? $value : $result;
    }

}
