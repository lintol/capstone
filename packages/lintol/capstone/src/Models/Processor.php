<?php

namespace Lintol\Capstone\Models;

use Illuminate\Database\Eloquent\Model;
use Alsofronie\Uuid\UuidModelTrait;
use App\User;

class Processor extends Model
{
    use UuidModelTrait;

    protected $casts = [
        'metadata' => 'json'
    ];

    protected $fillable = [
         'name',
         'description',
         'unique_tag',
         'module',
         'content'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class);
    }
}
