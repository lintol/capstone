<?php

namespace Lintol\Capstone\Models;

use Illuminate\Database\Eloquent\Model;
use Alsofronie\Uuid\UuidModelTrait;
use App\User;

class ProcessorConfiguration extends Model
{
    use UuidModelTrait;

    protected $casts = [
        'user_configuration_storage' => 'json',
        'definition' => 'json',
        'configuration' => 'json',
        'rules' => 'json'
    ];

    protected $fillable = [
         'user_configuration_storage',
         'configuration',
         'definition',
         'rules'
    ];

    public function processor()
    {
        return $this->belongsTo(Processor::class);
    }

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    public function buildDefinition()
    {
        return [
            'configuration' => $this->configuration,
            'definition' => $this->definition
        ];
    }
}
