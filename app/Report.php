<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    //
    protected $fillable = [ 
         'name',
         'user',
         'errors',
         'warnings',
         'passes',
         'qualityScore',
         'created_at',
         'updated_at'
    ];  
}
