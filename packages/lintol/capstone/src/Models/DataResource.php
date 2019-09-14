<?php

namespace Lintol\Capstone\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use Alsofronie\Uuid\UuidModelTrait;
use App\User;

class DataResource extends Model
{
    use UuidModelTrait;

    protected $fillable = [
         'filename',
         'url',
         'name',
         'filetype',
         'status',
         'remote_id',
         'user_id',
         'package_id',
         'ckan_instance_id',
         'source',
         'user',
         'archived',
         'reportid',
         'content',
         'settings',
         'size',
         'locale',
         'organization'
    ];

    public $present = true;

    public function creator()
    {
        return $this->belongsTo(User::class);
    }

    public $casts = [
        'settings' => 'json',
        'source' => 'json'
    ];

    public function run()
    {
        return $this->hasMany(ValidationRun::class);
    }

    public function setStatus($status)
    {
        if ($this->id) {
            $this->status = $status;
            $change = new DataResourceStatusChange;
            $change->data_resource_id = $this->id;
            $change->save();
        }
    }

    public function resourceable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function package()
    {
        return $this->belongsTo(DataPackage::class);
    }

    public function summaryByStatus()
    {
        return DB::table('data_resources')
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();
    }
}
