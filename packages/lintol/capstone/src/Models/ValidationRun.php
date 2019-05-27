<?php

namespace Lintol\Capstone\Models;

use App\User;
use Carbon\Carbon;
use Event;
use Illuminate\Database\Eloquent\Model;
use Alsofronie\Uuid\UuidModelTrait;
use Lintol\Capstone\Events\ResultRetrievedEvent;

class ValidationRun extends Model
{
    use UuidModelTrait;

    protected $dates = [
        'created_at',
        'updated_at',
        'requested_at',
        'initiated_at',
        'completed_at'
    ];

    protected $casts = [
        'settings' => 'json',
        'doorstep_definition' => 'json'
    ];

    const STATUS_UNKNOWN = 0;
    const STATUS_SUCCEEDED = 1;
    const STATUS_FAILED = 2;
    const STATUS_RUNNING = 3;

    public function creator()
    {
        return $this->belongsTo(User::class);
    }

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

        \Log::info(__("Announcing run ") . $this->id . __(" has finished"));
        Event::fire(new ResultRetrievedEvent($this->id));
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

        \Log::info($this->dataResource->package);
        \Log::info($this->dataResource->package_id);
        if ($this->dataResource->package) {
            $definition['package'] = $this->dataResource->package->metadata;
        }
        $this->doorstep_definition = $definition;

        return true;
    }
}
