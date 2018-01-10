<?php

namespace Lintol\Capstone\Transformers;

use League\Fractal;
use Lintol\Capstone\Models\Data;

class DataTransformer extends Fractal\TransformerAbstract
{
    public function transform(Data $data)
    {
        return [
            'id' => $data->id,
            'name' => $data->name,
            'filename' => $data->filename,
            'uri' => $data->uri
        ];
    }
}
