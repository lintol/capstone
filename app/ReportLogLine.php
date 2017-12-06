<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReportLogLine extends Model
{
    //  
    protected $fillable = [ 
         'ragType',
         'message',
         'processor',
         'detail',
         'created_at',
         'updated_at'
    ]; 
}
