<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class SurveyQuestionAnswer extends Model {

    protected $table="survey_question_answers";
    protected $fillable = ['survey_id','question_id','name','number_of_selections','firebase_id'];

    protected $dates = [];

    public static $rules = [
        // Validation rules
    ];

    public $timestamps=false;
    // Relationships

}
