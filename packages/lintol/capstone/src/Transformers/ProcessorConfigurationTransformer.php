<?php

namespace Lintol\Capstone\Transformers;

use League\Fractal;
use Lintol\Capstone\Models\Processor;
use Lintol\Capstone\Models\ProcessorConfiguration;

class ProcessorConfigurationTransformer extends Transformer
{
    protected $defaultIncludes = [
        'processor'
    ];

    public static function inputMapping()
    {
        return [
            'userConfigurationStorage' => 'user_configuration_storage',
            'processor' => function ($processor) {
                return ['processor_id' => $processor['id']];
            }
        ];
    }

    public function transform(ProcessorConfiguration $configuration)
    {
        return [
            'id' => $configuration->id,
            'userConfigurationStorage' => $configuration->user_configuration_storage
        ];
    }

    public function includeProcessor(ProcessorConfiguration $configuration)
    {
        return $this->item($configuration->processor, new ProcessorTransformer, 'processors');
    }
}
