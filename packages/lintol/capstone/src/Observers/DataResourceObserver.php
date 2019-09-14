<?php

namespace Lintol\Capstone\Observers;

use Lintol\Capstone\Models\DataResource;
use Lintol\Capstone\ValidationProcess;

class DataResourceObserver
{
    /**
     * Watch for the ProcessorConfiguration saving event.
     *
     * @param ProcessorConfiguration $processorConfiguration
     * @return void
     */
    public function saving(DataResource $resource)
    {
        \Log::info('saving data resource with status ' . $resource->status);

        if ($resource->status == 'ready to process') {
            $resource->setStatus('processors checked');
            $resource->save();
            ValidationProcess::launch($resource);
        }
    }
}
