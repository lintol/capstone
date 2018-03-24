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
            'userConfigurationStorage' => function ($userConfigurationStorage) {
                return ['user_configuration_storage' => json_decode($userConfigurationStorage)];
            },
            'processor' => function ($processor) {
                return ['processor_id' => $processor['id']];
            }
        ];
    }

    public function transform(ProcessorConfiguration $configuration)
    {
        return [
            'id' => $configuration->id,
            'userConfigurationStorage' => json_encode($configuration->user_configuration_storage, JSON_FORCE_OBJECT)
        ];
    }

    public function includeProcessor(ProcessorConfiguration $configuration)
    {
        return $this->item($configuration->processor, new ProcessorTransformer, 'processors');
    }
}
