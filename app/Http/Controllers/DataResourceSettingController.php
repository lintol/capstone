<?php

namespace App\Http\Controllers;

use Auth;
use DB;
use Illuminate\Http\Request;
use Lintol\Capstone\Models\ProcessorConfiguration;
use Lintol\Capstone\Transformers\DataResourceTransformer;
use Lintol\Capstone\Models\Profile;
use Lintol\Capstone\Models\DataResource;
use Lintol\Capstone\Observer\ProcessorConfigurationObserver;
use Lintol\Capstone\ResourceManager;

class DataResourceSettingController extends Controller
{
    public $dataResourceTransformer;

    public function __construct(DataResourceTransformer $dataResourceTransformer, ResourceManager $resourceManager)
    {
        $this->dataResourceTransformer = $dataResourceTransformer;
        $this->resourceManager = $resourceManager;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // $this->authorize('update', DataResource::class);
        $setting = $request->all();

        if (!is_array($setting)) {
            abort(400, __("Setting must be provided as an object"));
        }

        if (!array_key_exists('dataProfileId', $setting)) {
            abort(400, __("Only profile ID settings are currently supported"));
        }

        if (!array_key_exists('dataResourceUrls', $setting)) {
            abort(400, __("Data resource URL is required for setting"));
        }

        $profileId = Profile::findOrFail($setting['dataProfileId'])->id;

        $dataResourceUrls = collect($setting['dataResourceUrls']);

        $user = Auth::user();
        $dataResources = collect();

        $dataResourceUrls->each(function ($dataResourceUrl) use ($profileId, $user, &$dataResources) {
            if ($dataResourceUrl['id']) {
                if (strpos($dataResourceUrl['id'], 'remote-') === 0) {
                    $remoteId = substr($dataResourceUrl['id'], 7);
                    $dataResource = $this->resourceManager->getProvider()->getDataResource($remoteId);

                    if (!$dataResource) {
                        abort(__("Data resource not found"));
                    }
                } else {
                    $dataResource = DataResource::findOrFail($dataResourceUrl['id']);
                }
            } else {
                $dataResource = $this->resourceManager->find($dataResourceUrl['url'], $user);
            }

            if (!$dataResource) {
                $dataResource->settings = [
                  'name' => $dataResourceUrl['url']
                ];

                if ($user) {
                  $dataResource->user_id = Auth::user()->id;
                }

                $dataResource->source = '';
                $dataResource->url = $dataResourceUrl;
                $dataResource->filetype = $dataResourceUrl['filetype'];
            }
            $settings = $dataResource->settings;
            $settings['dataProfileId'] = $profileId;
            $dataResource->settings = $settings;

            $dataResource = $this->resourceManager->onboard($dataResource);
            if ($dataResource) {
                $dataResources->push($dataResource);
            }
        });

        return fractal()
            ->collection($dataResources, $this->dataResourceTransformer, 'dataResources')
            ->respond();
    }
}
