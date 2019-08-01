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

    public function duplicate()
    {
        $dupe = new self();
        $dupe->data_resource_id = $this->data_resource_id;
        $dupe->profile_id = $this->profile_id;
        return $dupe;
    }

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
        Event::dispatch(new ResultRetrievedEvent($this->id));
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

        $allMetadataOnly = true;
        foreach ($definitions as $def) {
            $configuration = $def['configuration'];
            if (! array_key_exists('metadataOnly', $configuration) || ! $configuration['metadataOnly']) {
                $allMetadataOnly = false;
            }
        }
        $settings['allMetadataOnly'] = $allMetadataOnly;

        $this->settings = $settings;

        $definition = [
            'definitions' => $definitions,
            'settings' => $settings
        ];

        $definition['context'] = [];
        if ($this->dataResource->package) {
            $definition['context']['package'] = $this->dataResource->package->metadata;
        }

        $this->doorstep_definition = $definition;

        return true;
    }
}
