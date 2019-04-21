<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Extension extends Model
{
    public $table = 'extensions';



    public $fillable = [
        'user_id',
        'extension_no',
        'description'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'user_id' => 'integer',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'user_id' => 'required',
        'extension_no' => 'required'
    ];

    public function user()
    {
        return $this->belongTo('App\User');
    }
}
