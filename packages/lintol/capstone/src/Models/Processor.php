<?php

namespace Lintol\Capstone\Models;

use Illuminate\Database\Eloquent\Model;
use Alsofronie\Uuid\UuidModelTrait;
use App\User;

class Processor extends Model
{
    use UuidModelTrait;

    protected $casts = [
        'rules' => 'json',
        'definition' => 'json'
    ];

    protected $fillable = [
         'name',
         'description',
         'unique_tag',
         'module',
         'content',
         'rules',
         'definition',
         'configuration_options',
         'configuration_defaults'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class);
    }

    public function configurations()
    {
        return $this->hasMany(ProcessorConfiguration::class);
    }
}
