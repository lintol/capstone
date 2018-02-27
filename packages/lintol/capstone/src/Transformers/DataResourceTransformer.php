<?php

namespace Lintol\Capstone\Transformers;

use League\Fractal;
use Lintol\Capstone\Models\DataResource;

class DataResourceTransformer extends Fractal\TransformerAbstract
{
    public function transform(DataResource $data)
    {
        return [
            'id' => $data->id,
            'filename' => $data->filename,
            'url' => $data->url,
            'filetype' => $data->filetype,
            'status' => $data->status,
            'stored' => $data->stored,
            'user' => $data->user,
            'archived' => $data->archived,
            'reportid' => $data->reportid
        ];
    }
}
