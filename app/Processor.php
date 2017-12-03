<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Processor extends Model
{
    //
     protected $fillable = [ 
         'name',
         'description',
         'creator',
         'uniqueTag',
         'created_at',
         'updated_at'
    ]; 
}
