<?php

namespace App;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Alsofronie\Uuid\UuidModelTrait;

class Report extends Model
{
    use UuidModelTrait;

    protected $fillable = [ 
         'name',
         'errors',
         'warnings',
         'passes',
         'quality_score'
    ];

    public function owner()
    {
        return $this->belongsTo(User::class);
    }
}
