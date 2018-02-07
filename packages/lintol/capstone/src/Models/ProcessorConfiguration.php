<?php

namespace Lintol\Capstone\Models;

use Illuminate\Database\Eloquent\Model;
use Alsofronie\Uuid\UuidModelTrait;
use App\User;

class ProcessorConfiguration extends Model
{
    use UuidModelTrait;

    protected $casts = [
        'definition' => 'json',
        'configuration' => 'json',
        'rules' => 'json'
    ];

    protected $fillable = [
         'user_configuration_storage',
         'processor_id',
         'configuration',
         'definition',
         'rules'
    ];

    public function filters()
    {
        return [];
    }

    public function rules()
    {
        return [];
    }

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
        $this->configuration = json_decode($this->user_configuration_storage);

        return [
            'configuration' => $this->configuration,
            'definition' => $this->definition
        ];
    }
}
