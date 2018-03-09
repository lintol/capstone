<?php

namespace Lintol\Capstone\Models;

use Illuminate\Database\Eloquent\Model;
use Alsofronie\Uuid\UuidModelTrait;
use App\User;

class DataResource extends Model
{
    use UuidModelTrait;

    protected $fillable = [
         'filename',
         'url',
         'filetype',
         'status',
         'stored',
         'user_id',
         'archived',
         'reportid',
         'content'
    ];

    public $present = true;

    public function creator()
    {
        return $this->belongsTo(User::class);
    }

    public $casts = [
        'settings' => 'json'
    ];

    public function run()
    {
        return $this->hasMany(ValidationRun::class);
    }

    public function resourceable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
