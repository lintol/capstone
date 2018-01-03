<?php

namespace Lintol\Capstone\Models;

use Illuminate\Database\Eloquent\Model;
use Alsofronie\Uuid\UuidModelTrait;

class Validation extends Model
{
    use UuidModelTrait;

    protected $casts = [
        'metadata' => 'json'
    ];

    const STATUS_UNKNOWN = 0;
    const STATUS_SUCCEEDED = 1;
    const STATUS_FAILED = 2;
    const STATUS_RUNNING = 3;

    public function configuration()
    {
        return $this->belongsTo(ProcessorConfiguration::class);
    }

    public function data()
    {
        return $this->belongsTo(Data::class);
    }

    public function report()
    {
        return $this->hasOne(Report::class);
    }

    public function buildMetadata($metadata)
    {
        $metadata = array_merge(
            $this->configuration->processor->metadata,
            $this->configuration->metadata
        );
        $metadata['runtime'] = $metadata;
        $this->metadata = $metadata;

        return $this->metadata;
    }
}
