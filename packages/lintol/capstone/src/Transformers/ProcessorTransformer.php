<?php

namespace Lintol\Capstone\Transformers;

use League\Fractal;
use Lintol\Capstone\Models\Processor;

class ProcessorTransformer extends Fractal\TransformerAbstract
{
    public function transform(Processor $processor)
    {
        return [
            'id' => $processor->id,
            'name' => $processor->name,
            'description' => $processor->description,
            'creatorId' => $processor->creator_id,
            'uniqueTag' => $processor->unique_tag,
            'module' => $processor->module,
            'created_at' => $processor->created_at,
            'updated_at' => $processor->updated_at,
            'configurationCount' => $processor->configurations()->count(),
            'configurationOptions' => $processor->configuration_options,
            'configurationDefaults' => $processor->configuration_defaults
        ];
    }
}
