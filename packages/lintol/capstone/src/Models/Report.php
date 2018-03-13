<?php

namespace Lintol\Capstone\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Alsofronie\Uuid\UuidModelTrait;

class Report extends Model
{
    use UuidModelTrait;

    public $fillable = [ 
        'name',
        'errors',
        'warnings',
        'passes',
        'quality_score'
    ];

    public $casts = [
        'content' => 'json'
    ];

    public function owner()
    {
        return $this->belongsTo(User::class);
    }

    public function make($result, ValidationRun $run)
    {
        $report = new self;

        if ($run->data_resource_id && $run->profile_id) {
            $count = $this->whereHas('run', function ($query) use ($run) {
                return $query->whereDataResourceId($run->data_resource_id)
                  ->whereProfileId($run->profile_id);
            })->count();

            $report->name = $run->profile->name . ' | ' . $run->dataResource->filename . ' | ' . ($count + 1);
        } else {
            $report->name = '(none)';
        }
        $report->content = json_decode($result);
        $content = json_decode($report->content, true);

        if (array_key_exists('error-count', $content)) {
            $report->errors = (int) $content['error-count'];
        }

        if (array_key_exists('warning-count', $content)) {
            $report->warnings = (int) $content['warning-count'];
        }

        if (array_key_exists('information-count', $content)) {
            $report->passes = (int) $content['information-count'];
        }

        $report->quality_score = 0;

        $report->run()->associate($run);

        if ($run->creator) {
            $report->owner()->associate($run->creator);
        }

        return $report;
    }

    public function run()
    {
        return $this->belongsTo(ValidationRun::class);
    }
}
