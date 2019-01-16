<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Survey extends Model {

    protected $fillable = [];

    protected $dates = [];

    public static $rules = [
        // Validation rules
    ];

    // Relationships
    public function SurveyUsers()
    {
        return $this->belongsToMany('App\User', 'survey_users','survey_id','user_id');
    }

}
