<?php

namespace Lintol\Capstone\Models;

use Illuminate\Database\Eloquent\Model;
use Alsofronie\Uuid\UuidModelTrait;

class Validation extends Model
{
    use UuidModelTrait;

    const STATUS_UNKNOWN = 0;
    const STATUS_SUCCEEDED = 1;
    const STATUS_FAILED = 2;
    const STATUS_RUNNING = 3;

    public function processor()
    {
        return $this->belongsTo(Processor::class);
    }

    public function data()
    {
        return $this->belongsTo(Data::class);
    }
}
