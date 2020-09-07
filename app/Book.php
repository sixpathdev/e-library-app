<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    //
    public function user()
    {
        $this->belongsTo('App\User', 'uploaded_by', 'id');
    }
}
