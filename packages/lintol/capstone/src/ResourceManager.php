<?php

namespace Lintol\Capstone;

use Auth;
use Log;
use Illuminate\Support\FacadesLog;
use Socialite;
use Lintol\Capstone\CkanResourceProvider;
use Lintol\Capstone\Models\DataResource;
use Crypt;
use Laravel\Socialite\Two\CkanProvider;
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


    /**
     * Save the data resource information to the database.
     *
     * @param DataResource $dataResource
     * @return DataResource|null
     */
    public function onboard(DataResource $dataResource)
    {
        if (config('capstone.features.redirectable-content', false)) {
            $this->onboardRedirectable($dataResource);
            // Will be done in observer, now it has a status: ValidationProcess::launch($dataResource);
        } else {
            $this->saveDataResourceDetails($dataResource);
        }
        if ($dataResource->save()) {
            return $dataResource;
        }

        return null;
    }

    public function getOAuthDriver($name, $resourceable = null)
    {
        if ($name == 'ckan') {
            if ($resourceable) {
                $config = config('services.ckan');
                if ($resourceable->client_id && $resourceable->client_secret) {
                    $clientId = Crypt::decrypt($resourceable->client_id);
                    $clientSecret = Crypt::decrypt($resourceable->client_secret);
                    $config['client_id'] = $clientId;
                    $config['client_secret'] = $clientSecret;
                    $config['url'] = $resourceable->uri;
                }
                $driver = Socialite::buildProvider(
                    CkanProvider::class, $config
                );

                $driver->setRootUrl($resourceable->uri);

                $redirectUrl = $driver->getRedirectUrl() . '/' . $resourceable->id;
                $driver->redirectUrl($redirectUrl);
            } else {
                abort(400, __("You must provide a valid CKAN server to authenticate against."));
            }
        } else {
            $driver = Socialite::driver($name);
        }

        return $driver;
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

    /**
     * @param DataResource $dataResource
     */
    public function checkHead(DataResource $dataResource): array
    {
        $client = new GuzzleHttp\Client();

        $request = new GuzzleHttp\Psr7\Request('HEAD', $dataResource->url);

        $size = null;
        $missing = true;
        try {
            $response = $client->send($request, [
                'headers' => ['Accept-Encoding' => 'deflate, gzip'],
            ]);

            if ($response->hasHeader('Content-Type')) {
                $type = $response->getHeader('Content-Type')[0];
            } else if ($response->hasHeader('x-encoded-content-type')) {
                $type = $response->getHeader('x-encoded-content-type')[0];
            } else {
                $type = null;
            }
            Log::info("TYPE: " . $type);

            if ($response->hasHeader('Content-Length')) {
                $size = $response->getHeader('Content-Length')[0];
            } else if ($response->hasHeader('x-encoded-content-length')) {
                $size = $response->getHeader('x-encoded-content-length')[0];
            }

            $missing = false;
        } catch (\GuzzleHttp\Exception\ServerException $e) {
            $missing = $e->getResponse()->getStatusCode();
            $type = null;
            Log::info("SERVER ERROR: " . $missing);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $missing = $e->getResponse()->getStatusCode();
            $type = null;
            Log::info("CLIENT ERROR: " . $missing);
        }
        Log::info("SIZE: " . $size);
        return [$missing, $size, $type];
    }

    /**
     * @param DataResource $dataResource
     */
    public function onboardRedirectable(DataResource $dataResource, bool $getHead=True): void
    {
        $path = basename($dataResource->url);
        $dataResource->filename = $path;
        $dataResource->name = $path;
        $pathParts = pathinfo($path);
        if (!$dataResource->filetype && isset($pathParts['extension'])) {
            $dataResource->filetype = $pathParts['extension'];
        }

        $settings = $dataResource->settings;

        list($missing, $size, $type) = $this->checkHead($dataResource);
        if ($type) {
            $dataResource->filetype = $type;
        }
        $settings['fileType'] = $dataResource->filetype;
        $settings['locale'] = $dataResource->locale;

        if ($missing !== false) {
            $status = 'missing: ' . $missing;
        } else {
            $status = 'ready to process';
        }
        $dataResource->status = $status;
        $dataResource->size = $size;

        $settings['size'] = $size;
        $settings['organization'] = $dataResource->organization;
        $dataResource->settings = $settings;
        $dataResource->content = $dataResource->url;

        if ($dataResource->package && !$dataResource->package->id) {
            $dataResource->package->save();
            $dataResource->package_id = $dataResource->package->id;
        }
        $dataResource->save();
    }

    /**
     * A url is supplied by the user. The information about the data resource is
     * retrieved from the http get and the information is saved to the data base.
     * @param DataResource $dataResource
     */
    public function saveDataResourceDetails(DataResource $dataResource): void
    {
        $client = new GuzzleHttp\Client();

        $request = new GuzzleHttp\Psr7\Request('GET', $dataResource->url);
        $promise = $client->sendAsync($request)->then(function ($response) use ($dataResource) {
            $path = basename($dataResource->url);
            $dData = $response->getBody();

            $dataResource->filename = $path;
            $dataResource->name = $path;
            $pathParts = pathinfo($path);

            if ($response->hasHeader('Content-Type')) {
                $dataResource->filetype = $response->getHeader('Content-Type')[0];
            } else if ($response->hasHeader('x-encoded-content-type')) {
                $dataResource->filetype = $response->getHeader('x-encoded-content-type')[0];
            }
            if (!$dataResource->filetype && isset($pathParts['extension'])) {
                $dataResource->filetype = $pathParts['extension'];
            }

            $dataResource->status = 'ready to process';
            $settings = $dataResource->settings;
            $settings['fileType'] = $dataResource->filetype;
            $settings['locale'] = $dataResource->locale;
            $settings['size'] = strlen($dData);
            $settings['organization'] = $dataResource->organization;
            $dataResource->settings = $settings;
            $dataResource->content = $dData;
            $dataResource->save();

            // Will be done in observer, now it has a status: return ValidationProcess::launch($dataResource);
            return $dataResource;
        }, function ($error) {
            abort(400, __("Invalid data URI request"));
        });

        $promise->wait();
    }
}
