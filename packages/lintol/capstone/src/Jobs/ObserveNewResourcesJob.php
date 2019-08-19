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
                    $update = (count($res) > 4) && $res[4];
                    $metadata = json_decode($ini->context->package);
                    Log::info("[lintol-observe] " . __("New resource seen on " . $source) . " -- " . $resource->name . " in " . $metadata->name);

                    Log::info("[lintol-observe] Checking url: $resource->url");
                    $ckanInstance = CkanInstance::whereUri($source)->first();
                    if (! $ckanInstance) {
                        $ckanInstance = new CkanInstance;
                        $ckanInstance->fill([
                            'name' => $source,
                            'uri' => $source
                        ]);
                        $ckanInstance->save();
                    }

                    if ($update) {
                        Log::info("[lintol-observe] FORCE UPDATE is on");
                    }

                    $lastModified = Carbon::parse($metadata->metadata_modified);
                    $organization = $metadata->organization ? $metadata->organization->name : null;
                    $locale = config('app.locale');

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
                            if ($extra->key == 'default_locale' && $extra->value) {
                                $locale = $extra->value;
                            }
                        }
                    }

                    \Log::info($locale);

                    if (array_key_exists('harvestSource', $sourceObject)) {
                        $sourceObject['sourceChain'] = $sourceObject['lintolSource'] . '|' . $sourceObject['harvestSource'];
                    }

                    $package = DataPackage::whereRemoteId($metadata->id)->whereCkanInstanceId($ckanInstance->id)->first();
                    if (! $package || $update) {
                        if (! $package) {
                            $package = new DataPackage;
                        }
                        $package->fill([
                            'remote_id' => $metadata->id,
                            'ckan_instance_id' => $ckanInstance->id,
                            'metadata' => $metadata,
                            'name' => $metadata->name,
                            'url' => $metadata->url,
                            'locale' => $locale,
                            'source' => json_encode($sourceObject)
                        ]);
                        $package->save();
                        Log::debug("Added package: " . $metadata->name);
                    }

                    $res = DataResource::whereRemoteId($resourceId)->whereCkanInstanceId($ckanInstance->id)->first();
                    if (! $res || $res->updated_at->lt($lastModified) || $update) {
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
                            'status' => 'new resource',
                            'organization' => $organization,
                            'locale' => $locale,
                            'source' => json_encode($sourceObject)
                        ]);
                        $res->resourceable()->associate($ckanInstance);
                        $res->save();

                        $res = $resourceManager->onboard($res);
                        Log::info("Added resource: " . $res->name . " with remote ID " . $res->remote_id . " and status " . $res->status);
                    }
                });
        }, false);

        Log::info(__("Subscription exited."));
    }
}
