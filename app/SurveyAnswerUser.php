<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class SurveyAnswerUser extends Model {

    protected $table="survey_answer_users";
    protected $fillable = ['answer_id','user_id'];

    protected $dates = [];

    public static $rules = [
        // Validation rules
    ];

    public $timestamps=false;

    // Relationships

}
