<?php

namespace Lintol\Capstone\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Alsofronie\Uuid\UuidModelTrait;

class ValidationRun extends Model
{
    use UuidModelTrait;

    protected $casts = [
        'settings' => 'json',
        'doorstep_definition' => 'json'
    ];

    const STATUS_UNKNOWN = 0;
    const STATUS_SUCCEEDED = 1;
    const STATUS_FAILED = 2;
    const STATUS_RUNNING = 3;

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    public function dataResource()
    {
        return $this->belongsTo(DataResource::class, 'data_resource_id');
    }

    public function markCompleted()
    {
        $this->completed_at = Carbon::now();
        $this->save();

        if ($this->dataResource) {
          $this->dataResource->status = 'report run';
          $this->dataResource->save();
        }
    }

    public function report()
    {
        return $this->hasOne(Report::class, 'run_id');
    }

    public function buildDefinition($settings)
    {
        $definitions = $this->profile->buildDefinitions($settings);

        if (!$definitions) {
            \Log::info(__("No profile definition"));
            return false;
        }

        $this->settings = $settings;

        $definition = [
            'definitions' => $definitions,
            'settings' => $settings
        ];
        $this->doorstep_definition = $definition;

        return true;
    }
}
