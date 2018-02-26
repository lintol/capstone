<?php

namespace Lintol\Capstone\Models;

use Illuminate\Database\Eloquent\Model;
use App\User;

class DataResource extends Model
{
    //
    protected $fillable = [
         'filename',
         'url',
         'filetype',
         'status',
         'stored',
         'user',
         'archived',
         'reportid'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class);
    }
}
