<?php

namespace Lintol\Capstone;

use Auth;
use Lintol\Capstone\CkanResourceProvider;
use Lintol\Capstone\Models\DataResource;
use GuzzleHttp;

class ResourceManager
{
    public function find($url, $user)
    {
        $existingResource = DataResource::whereUrl($url);
        if ($user) {
            $existingResource = $existingResource->whereUserId($user->id);
        }
        $existingResource = $existingResource->first();

        return $existingResource;
    }

    public function onboard(DataResource $dataResource)
    {
        $client = new GuzzleHttp\Client();
        $request = new GuzzleHttp\Psr7\Request('GET', $dataResource->url);

        $promise = $client->sendAsync($request)->then(function ($response) use ($dataResource) {
            $path = basename($dataResource->url);
            $dData = $response->getBody();

            $dataResource->filename = $path;
            $dataResource->name = $path;
            $pathParts = pathinfo($path);
            $dataResource->filetype = $pathParts['extension'];
            $dataResource->status = 'new resource';
            $settings = $dataResource->settings;
            $settings['fileType'] = $dataResource->filetype;
            $dataResource->settings = $settings;
            $dataResource->content = $dData;
            $dataResource->save();

            ValidationProcess::launch($dataResource);
        }, function ($error) {
            abort(400, __("Invalid data URI request"));
        });

        $promise->wait();

        if ($dataResource->save()) {
            return $dataResource;
        }

        return null;
    }

    public function getProvider()
    {
        $currentUser = Auth::user();

        $resourceProvider = null;

        if ($currentUser) {
            $remoteUser = $currentUser->primaryRemoteUser;

            if ($remoteUser) {
                $resourceable = $remoteUser->resourceable;

                if ($resourceable) {
                    switch ($remoteUser->driver) {
                        case 'ckan':
                            $resourceProvider = CkanResourceProvider::generate($resourceable, $remoteUser);
                            break;
                        default:
                    }
                }
            }
        }

        return $resourceProvider;
    }
}
