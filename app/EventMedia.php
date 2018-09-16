<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class EventMedia extends Model {
    protected $primaryKey = 'id';
    protected $table = 'event_media';

    protected $fillable = ['event_id','link','type'];
    public $timestamps = false;

    // relations

    // 1 entity belongs to many entity_localizations
    public function event() {
        return $this->belongsTo('App\Event');
    }

    public function getLinkAttribute($value){
        if($this->type == 1){
            $base_url = ENV('FOLDER');
            $photo = $base_url.$value;
            return $photo;

        }
        return $value;

    }

}
