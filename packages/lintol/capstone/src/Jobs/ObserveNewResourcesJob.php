<?php

namespace Lintol\Capstone\Jobs;

use League\OAuth2\Server\CryptKey;
use File;
use GuzzleHttp;
use App;
use Lintol\Capstone\Models\ValidationRun;
use Lintol\Capstone\Models\Processor;
use Lintol\Capstone\Models\DataResource;
use Lintol\Capstone\ResourceManager;
use Lintol\Capstone\Models\CkanInstance;
use Thruway\ClientSession;
use Thruway\Peer\Client;
use Thruway\Transport\PawlTransportProvider;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Log;
use Thruway\Logging\Logger;
use Carbon\Carbon;
use Lintol\Capstone\WampConnection;
use Lintol\Capstone\Models\DataPackage;

use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\ValidationData;
use League\OAuth2\Server\Exception\OAuthServerException;
use Laravel\Passport\TokenRepository;
use Laravel\Passport\Passport;

class ObserveNewResourcesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $validation = null;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(WampConnection $wampConnection, CkanInstance $ckanFactory, ResourceManager $resourceManager)
    {
        Log::info(__("Subscribing."));

        Logger::set(Log::driver());

        $wampConnection->execute(function (ClientSession $session) use ($ckanFactory, $resourceManager) {
                Log::info("[lintol-observe] " . __("Connected and subscribing to result events."));
                $client = new GuzzleHttp\Client();
                Log::info("[lintol-observe] " . __("Will make HEAD request to any new resources."));

                $session->subscribe('com.ltldoorstep.event_found_resource', function ($res) use ($session, $resourceManager, $client) {
                    Log::info("[lintol-observe] com.ltldoorstep.event_found_resource!");
                    $resourceId = $res[0];
                    $resource = $res[1];
                    $ini = $res[2];
                    $source = $res[3];
                    $metadata = json_decode($ini->context->package);
                    Log::info("[lintol-observe] " . __("New resource seen on " . $source) . " -- " . $resource->name . " in " . $metadata->name);

                    Log::info("[lintol-observe] Checking url: $resource->url");
                    $request = new GuzzleHttp\Psr7\Request('HEAD', $resource->url);

                    $size = null;
                    $missing = true;
                    try {
                        $response = $client->send($request, [
                            'headers' => ['Accept-Encoding' => 'deflate, gzip'],
                        ]);

                        if ($response->hasHeader('Content-Length')) {
                            $size = $response->getHeader('Content-Length')[0];
                        } else if ($response->hasHeader('x-encoded-content-length')) {
                            $size = $response->getHeader('x-encoded-content-length')[0];
                        }

                        $missing = false;
                    } catch (\GuzzleHttp\Exception\ServerException $e) {
                        $missing = $e->getResponse()->getStatusCode();
                        Log::info("SERVER ERROR: " . $missing);
                    } catch (\GuzzleHttp\Exception\ClientException $e) {
                        $missing = $e->getResponse()->getStatusCode();
                        Log::info("CLIENT ERROR: " . $missing);
                    }
                    Log::info("SIZE: " . $size);
                    if ($missing !== false) {
                        $status = 'missing: ' . $missing;
                    } else {
                        $status = 'valid link';
                    }

                    Log::info("[lintol-observe] Checked url: $resource->url");
                    $ckanInstance = CkanInstance::whereUri($source)->first();
                    if (! $ckanInstance) {
                        $ckanInstance = new CkanInstance;
                        $ckanInstance->fill([
                            'name' => $source,
                            'uri' => $source
                        ]);
                        $ckanInstance->save();
                    }

                    $lastModified = Carbon::parse($metadata->metadata_modified);

                    $sourceObject = ['lintolSource' => $source, 'lintolCkanInstanceId' => $ckanInstance->id];
                    if (property_exists($metadata, 'extras')) {
                        foreach ($metadata->extras as $extra) {
                            if ($extra->key == 'harvest_title') {
                                $sourceObject['harvestTitle'] = $extra->value;
                            }
                            if ($extra->key == 'harvest_source') {
                                $sourceObject['harvestSource'] = $extra->value;
                            }
                            if ($extra->key == 'harvest_url') {
                                $sourceObject['sourceUrl'] = $extra->value;
                            }
                        }
                    }
                    \Log::info($sourceObject);

                    if (array_key_exists('harvestSource', $sourceObject)) {
                        $sourceObject['sourceChain'] = $sourceObject['lintolSource'] . '|' . $sourceObject['harvestSource'];
                    }

                    $package = DataPackage::whereRemoteId($metadata->id)->whereCkanInstanceId($ckanInstance->id)->first();
                    if (! $package) {
                        $package = new DataPackage;
                        $package->fill([
                            'remote_id' => $metadata->id,
                            'ckan_instance_id' => $ckanInstance->id,
                            'metadata' => $metadata,
                            'name' => $metadata->name,
                            'url' => $metadata->url,
                            'source' => json_encode($sourceObject)
                        ]);
                        $package->save();
                        Log::debug("Added package: " . $metadata->name);
                    }

                    $res = DataResource::whereRemoteId($resourceId)->whereCkanInstanceId($ckanInstance->id)->first();
                    if (! $res || $res->updated_at->lt($lastModified)) {
                        if (! $res) {
                            $res = new DataResource;
                        }
                        $name = $resource->name;
                        if (! $name) {
                            $name = basename($resource->url);
                        }

                        $res->fill([
                            'remote_id' => $resourceId,
                            'ckan_instance_id' => $ckanInstance->id,
                            'content' => '',
                            'name' => $name,
                            'url' => $resource->url,
                            'package_id' => $package->id,
                            'filename' => basename($resource->url),
                            'filetype' => $resource->format,
                            'settings' => ['autorun' => true],
                            'status' => $status,
                            'size' => $size,
                            'source' => json_encode($sourceObject)
                        ]);
                        $res->resourceable()->associate($ckanInstance);
                        $res->save();

                        if ($missing) {
                            Log::info("Added missing resource: " . $res->name . " with remote ID " . $res->remote_id);
                        } else {
                            $res = $resourceManager->onboard($res);
                            Log::info("Added resource: " . $res->name . " with remote ID " . $res->remote_id);
                        }
                    }
                });
        }, false);

        Log::info(__("Subscription exited."));
    }
}
