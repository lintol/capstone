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

    public function make($result)
    {
        $report = new self;
        $report->name = '(none)';
        $report->content = json_decode($result);

        $report->errors = 0;
        $report->warnings = 0;
        $report->passes = 0;
        $report->quality_score = 0;

        return $report;
    }

    public function validation()
    {
        return $this->belongsTo(Validation::class);
    }
}
