<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $fillable = [ 
         'name',
         'description',
         'creator',
         'version',
         'uniqueTag',
         'created_at',
         'updated_at'
    ];
    //
}
