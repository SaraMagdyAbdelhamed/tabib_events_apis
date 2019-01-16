<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class surveyUser extends Model {

    protected $primaryKey = 'id';
    protected $table = 'survey_users';
    protected $fillable = ['survey_id','user_id'];

    protected $dates = [];

    public static $rules = [
        // Validation rules
    ];

    // Relationships

}
