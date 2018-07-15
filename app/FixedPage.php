<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class FixedPage extends Model {

    protected $fillable = [];

    protected $dates = [];

    public static $rules = [
        // Validation rules
    ];

    protected $hidden = [];
    // Relationships
    public function updated_by()
    {
        return $this->belongsTo("App\User","updated_by"); 
    }
    // Relationships

}
