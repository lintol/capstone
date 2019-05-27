<?php

namespace Lintol\Capstone\Jobs;

use League\OAuth2\Server\CryptKey;
use File;
use GuzzleHttp;
use App;
use Lintol\Capstone\Models\ValidationRun;
use Lintol\Capstone\Models\Processor;
use Lintol\Capstone\Models\DataResource;
use Lintol\Capstone\ValidationProcess;
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
    public function handle(WampConnection $wampConnection, CkanInstance $ckanFactory)
    {
        Log::info(__("Subscribing."));

        $wampConnection->execute(function (ClientSession $session) use ($ckanFactory) {
                Log::info("[lintol-observe] " . __("Connected and subscribing to result events."));

                $session->subscribe('com.ltldoorstep.event_found_resource', function ($res) use ($session) {
                    $resourceId = $res[0];
                    $resource = $res[1];
                    $ini = $res[2];
                    $source = $res[3];
                    $metadata = json_decode($ini->context->package);
                    Log::debug("[lintol-observe] " . __("New resource seen on " . $source) . " -- " . $resource->name . " in " . $metadata->name);

                    $ckanInstance = CkanInstance::whereUri($source)->first();
                    if (! $ckanInstance) {
                        $ckanInstance = new CkanInstance;
                        $ckanInstance->fill([
                            'name' => $source,
                            'uri' => $source
                        ]);
                        $ckanInstance->save();
                    }

                    $package = DataPackage::whereRemoteId($metadata->id)->whereSource($source)->first();
                    if (! $package) {
                        $package = new DataPackage;
                        $package->fill([
                            'remote_id' => $metadata->id,
                            'metadata' => $metadata,
                            'name' => $metadata->name,
                            'url' => $metadata->url,
                            'source' => $source
                        ]);
                        $package->save();
                        Log::debug("Added package: " . $metadata->name);
                    }

                    $res = DataResource::whereRemoteId($resourceId)->whereSource($source)->first();
                    if (! $res) {
                        $res = new DataResource;
                        $res->fill([
                            'remote_id' => $metadata->id,
                            'content' => '',
                            'name' => $resource->name,
                            'url' => $resource->url,
                            'filename' => basename($resource->url),
                            'filetype' => $resource->format,
                            'source' => $source
                        ]);
                        $res->resourceable()->associate($ckanInstance);
                        $res->save();
                        Log::debug("Added resource: " . $res->name);
                    }
                });
        }, false);

        Log::info(__("Subscription exited."));
    }
}
