<?php

namespace Lintol\Capstone\Models;

use Illuminate\Database\Eloquent\Model;
use Alsofronie\Uuid\UuidModelTrait;

class Data extends Model
{
    use UuidModelTrait;

    public $fillable = [
        'filename',
        'content'
    ];

    public $casts = [
        'settings' => 'json'
    ];

    public function run()
    {
        return $this->hasMany(ValidationRun::class);
    }
}
