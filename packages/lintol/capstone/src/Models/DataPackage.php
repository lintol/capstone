<?php

namespace Lintol\Capstone\Models;

use Illuminate\Database\Eloquent\Model;
use Alsofronie\Uuid\UuidModelTrait;
use App\User;

class DataPackage extends Model
{
    use UuidModelTrait;

    protected $fillable = [
         'name',
         'metadata',
         'remote_id',
         'url',
         'source',
         'ckan_instance_id',
         'user',
         'creator',
         'locale',
         'archived'
    ];

    public $present = true;

    public function creator()
    {
        return $this->belongsTo(User::class);
    }

    public $casts = [
        'metadata' => 'json',
        'source' => 'json'
    ];

    public function resources()
    {
        return $this->hasMany(DataResource::class);
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
