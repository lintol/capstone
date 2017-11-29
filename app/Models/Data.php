<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Alsofronie\Uuid\UuidModelTrait;

class Data extends Model
{
    use UuidModelTrait;

    public function validation()
    {
        return $this->hasMany(Validation::class);
    }
}