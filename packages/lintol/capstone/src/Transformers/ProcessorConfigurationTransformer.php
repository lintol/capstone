<?php

namespace Lintol\Capstone\Transformers;

use League\Fractal;
use Lintol\Capstone\Models\Processor;
use Lintol\Capstone\Models\ProcessorConfiguration;

class ProcessorConfigurationTransformer extends Fractal\TransformerAbstract
{
    protected $defaultIncludes = [
        'processor'
    ];

    public function transform(ProcessorConfiguration $configuration)
    {
        return [
            'id' => $configuration->id
        ];
    }

    public function includeProcessor(ProcessorConfiguration $configuration)
    {
        return $this->item($configuration->processor, new ProcessorTransformer, 'processors');
    }
}
