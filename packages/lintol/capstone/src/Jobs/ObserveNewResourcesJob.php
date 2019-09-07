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

                    ProcessNewResourcesJob::dispatch(
                        $resourceId,
                        $resource,
                        $ini,
                        $source,
                        $update,
                        $metadata
                    );
                });
        }, false);

        Log::info(__("Subscription exited."));
    }
}
