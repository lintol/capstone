<?php

namespace Lintol\Capstone\Models;

use DB;
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

    public function duplicate($replicate=false)
    {
        $dupe = new self();
        $dupe->data_resource_id = $this->data_resource_id;
        $dupe->profile_id = $this->profile_id;

        if ($replicate) {
            $dupe->doorstep_definition = $this->doorstep_definition;
            $dupe->settings = $this->settings;
        } else {
            $dupe->buildDefinition($this->settings);
        }

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

    public function summaryByStatus()
    {
        return DB::table('validation_runs')
            ->select('completion_status', DB::raw('count(*) as total'))
            ->groupBy('completion_status')
            ->pluck('total', 'completion_status')
            ->all();
    }

    public function markCompleted($success=true)
    {
        $this->completed_at = Carbon::now();
        $this->completion_status = $success ? self::STATUS_SUCCEEDED : self::STATUS_FAILED;
        $this->save();

        if ($this->dataResource) {
            if ($success) {
                $this->dataResource->setStatus('report run');
            } else {
                $this->dataResource->setStatus('report failed');
            }
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

        if (array_key_exists('locale', $settings)) {
            $definition['lang'] = $settings['locale'];
        }

        $definition['context'] = [];

        // Find without relations
        $fields = [
             'filename',
             'url',
             'name',
             'filetype',
             'status',
             'remote_id',
             'package_id',
             'ckan_instance_id',
             'source',
             'archived',
             'reportid',
             'settings',
             'size',
             'locale',
             'organization'
        ];

        $definition['context']['resource'] = [];
        foreach ($fields as $field) {
            $definition['context']['resource'][$field] = $this->dataResource->{$field};
        }

        if ($this->dataResource->package) {
            $definition['context']['package'] = $this->dataResource->package->metadata;
        }

        $this->doorstep_definition = $definition;

        return true;
    }
}
