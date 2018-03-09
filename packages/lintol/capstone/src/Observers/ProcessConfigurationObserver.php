<?php

namespace Lintol\Capstone\Observers;

use Lintol\Capstone\Models\ProcessorConfiguration;

class ProcessorConfigurationObserver
{
    /**
     * Watch for the ProcessorConfiguration saving event.
     *
     * @param ProcessorConfiguration $processorConfiguration
     * @return void
     */
    public function saving(ProcessorConfiguration $configuration)
    {
        \Log::info('saving configuration');
        $processor = $configuration->processor;
        if ($processor) {
            $configuration->rules = $processor->rules;
            $configuration->definition = $processor->definition;
        }
    }
}
