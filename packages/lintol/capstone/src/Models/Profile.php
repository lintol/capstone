<?php

namespace Lintol\Capstone\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Alsofronie\Uuid\UuidModelTrait;

class Profile extends Model
{
    use UuidModelTrait;

    protected $fillable = [
         'name',
         'description',
         'version',
         'unique_tag'
    ];

    public function configurations()
    {
        return $this->hasMany(ProcessorConfiguration::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class);
    }
}
