<?php

namespace Lintol\Capstone\Models;

use Illuminate\Database\Eloquent\Model;

class DataResourceStatusChange extends Model
{
    public function dataResource() {
        return $this->belongsTo(DataResource::class, 'data_resource_id');
    }
}
