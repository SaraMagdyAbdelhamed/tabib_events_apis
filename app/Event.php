<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use App\Libraries\Helpers;
use Carbon\Carbon;

class Event extends Model {

    protected $primaryKey = 'id';
    protected $table = 'events';
    protected $dates = ['created_at', 'updated_at'];
    protected $hidden = ['pivot'];
   // protected $appends= ['is_going'];

    protected $fillable = ['name', 'description',
        'website','mobile','email','code',
        'address','longtuide','latitude',
        'venue','start_datetime','end_datetime',
        'suggest_big_event','show_in_mobile'
        ,'gender_id','age_range_id','is_paid',
        'use_ticketing_system','is_active','event_status_id','rejection_reason','created_by','updated_by','tele_code','is_backend'];

    /** Relations Section */

    public function getImageAttribute($value){
        $base_url = ENV('FOLDER');
        $photo = ($value =='' || is_null($value)) ? '':$base_url.$value;
        return $photo;
    }

    public  function media(){
        return $this->hasMany('App\EventMedia' , 'event_id');
    }
    public  function posts(){
        return $this->hasMany('App\EventPost','event_id');
    }
    public  function GoingUsers(){
        return $this->belongsToMany('App\User', 'user_going','event_id','user_id');
    }
    // public  function CalenderUsers(){
    //     return $this->belongsToMany('App\User', 'user_calendars','event_id','user_id')->withPivot('from_date','to_date');
    // }
    public  function EventOwner(){
        return $this->belongsTo('App\User');

    }
    public function tickets(){
        return $this->hasMany('App\EventTicket','event_id');
    }

    public function EventCategory()
    {
        return $this->belongsToMany('App\Category','event_categories','event_id','category_id');
    }
    /** End Relations Section */

    /**  Localizations Through accessors */

    public function getNameAttribute($value)
    {
        $result = (app('translator')->getLocale()=='en') ? Helpers::localization('events','name',$this->id,1) : Helpers::localization('events','name',$this->id,2);
        return ($result=='Error')? $value : $result;
    }
    public function getDescriptionAttribute($value)
    {
        $result = (app('translator')->getLocale()=='en') ? Helpers::localization('events','description',$this->id,1) : Helpers::localization('events','description',$this->id,2);
        return ($result=='Error')? $value : $result;
    }
    public function getVenueAttribute($value)
    {
        $result = (app('translator')->getLocale()=='en') ? Helpers::localization('events','venue',$this->id,1) : Helpers::localization('events','venue',$this->id,2);
        return ($result=='Error')? $value : $result;
    }
    /** End  Localizations Through accessors */




    public  static function UserGoingThisEvent($user){
        return static::query()->join('user_going','events.id','=','user_going.event_id')
               ->where('user_going.user_id',$user)
                ->first();
    }

    public static function BigEvents(){
    	return static::query()->join('big_events','events.id','=','big_events.event_id')
    		   ->select('events.*','big_events.sort_order');

    }

    public  static function EventsInCategories($categories_ids){
        return static::query()
            ->leftJoin('event_categories','events.id','=','event_categories.event_id')
            ->whereIn('event_categories.category_id',$categories_ids)
            ->select('events.*')
            ->distinct()
            ->with('categories')
            ->with('prices.currency')
            ->with('hash_tags');

    }

    public static function event_entity_ar(){
        return static::query()->join('entity_localizations','events.id','=','entity_localizations.item_id')
            ->where('entity_id','=',4)
            ->where('field','=','name')
            ->select('events.*');
    }

    /**  Query Scopes to make code readable */

    public function ScopeIsActive($query){
            return $query->where('is_active', '=', 1);
        }

    public function ScopeShowInMobile($query){
        return $query->where('show_in_mobile', '=', 1);
    }

    public  function ScopeSuggestedAsBigEvent($query){
        return $query->where('suggest_big_event', '=', 1);

    }

    public function ScopeUpcomingEvents($query){
        return $query->where("end_datetime",'>=',Carbon::now());

    }
    public function ScopePastEvents($query){
        return $query->where("end_datetime",'<',Carbon::now());

    }
    public function ScopeWithPaginate($query,$page,$limit){
        return $query->skip(($page-1)*$limit)->take($limit);
    }
    public function ScopeThisMonthEvents($query){
        return $query->whereBetween("end_datetime",[Carbon::now(),Carbon::now()->endOfMonth()]);

    }
    public function ScopeNextMonthEvents($query){
        return $query->whereMonth("end_datetime",Carbon::now()->addMonth()->month);
 
    }
    public function ScopeCreatedByUser($query,$user){
        return $query->Where('created_by','=',$user->id);
    }

    public function ScopeNotCreatedByUser($query,$user){
        return $query->Where('created_by','!=',$user->id)->IsActive();

    }

    public function ScopeStartOfMothEvents($query){
        return $query->whereBetween("end_datetime",[Carbon::now()->startOfMonth(),Carbon::now()]);
    }

    /**
     * Query builder scope to list neighboring locations
     * within a given distance from a given location
     *
     * @param  Illuminate\Database\Query\Builder  $query  Query builder instance
     * @param  mixed                              $lat    Lattitude of given location
     * @param  mixed                              $lng    Longitude of given location
     * @param  integer                            $radius Optional distance
     * @param  string                             $unit   Optional unit
     *
     * @return Illuminate\Database\Query\Builder          Modified query builder
     */
    public function scopeDistance($query, $lat, $lng, $unit = "km")
    {
        $unit = ($unit === "km") ? 6378.10 : 3963.17;
        $lat = (float) $lat;
        $lng = (float) $lng;
        // $radius = (double) $radius;
        return $query ->select(DB::raw("*,
                            ($unit * ACOS(COS(RADIANS($lat))
                                * COS(RADIANS(latitude))
                                * COS(RADIANS($lng) - RADIANS(longtuide))
                                + SIN(RADIANS($lat))
                                * SIN(RADIANS(latitude)))) AS distance")
            )->orderBy('distance','asc');
    }


}
