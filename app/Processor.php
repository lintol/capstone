<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Processor extends Model
{
    //
     protected $fillable = [ 
         'name',
         'description',
         'moreInfo',
         'type'
    ]; 
}
