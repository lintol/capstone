<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StatusTracking extends Model
{
    public $casts = [
        'statuses' => 'json'
    ];
}
