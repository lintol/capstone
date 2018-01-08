<?php

namespace Lintol\Capstone\Models;

use Illuminate\Database\Eloquent\Model;
use Alsofronie\Uuid\UuidModelTrait;

class Rule extends Model
{
    use UuidModelTrait;

    public $fillable = [
        'definition'
    ];

    public $casts = [
        'definition' => 'json'
    ];

    public function run()
    {
        return $this->hasMany(ValidationRun::class);
    }
}
