<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Queue extends Model
{



    public function user()
    {
        return $this->belongTo('App\User');
    }
}
