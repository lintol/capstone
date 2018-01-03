<?php

namespace Lintol\Capstone\Models;

use Illuminate\Database\Eloquent\Model;
use Alsofronie\Uuid\UuidModelTrait;
use App\User;

class ProcessorConfiguration extends Model
{
    use UuidModelTrait;

    protected $casts = [
        'metadata' => 'json',
        'configuration' => 'json',
        'rules' => 'json'
    ];

     protected $fillable = [
         'configuration',
         'metadata',
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

    public function validation()
    {
        return $this->hasMany(Validation::class);
    }
}
