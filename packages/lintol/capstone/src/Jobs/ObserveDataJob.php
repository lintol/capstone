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

use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\ValidationData;
use League\OAuth2\Server\Exception\OAuthServerException;
use Laravel\Passport\Bridge\AccessTokenRepository;
use Laravel\Passport\Passport;

class ObserveDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $validation = null;

    protected $processFactory;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(ValidationProcess $processFactory, WampConnection $wampConnection)
    {
        Log::info(__("Subscribing."));

        $resourceServer = app(\League\OAuth2\Server\ResourceServer::class);
        $tokenRepository = app(\League\OAuth2\Server\ResourceServer::class);

        $wampConnection->execute(function (ClientSession $session) use ($processFactory) {
                Log::info("[lintol-observe] " . __("Connected and subscribing to result events."));

                $session->subscribe('com.ltldoorstep.event_result', function ($res) use ($session, $processFactory) {
                    $process = $processFactory->fromDataSession($res[0], $res[1], $session);

                    Log::debug("[lintol-observe] " . __("Validation result event seen."));

                    if ($process) {
                        Log::info("[lintol-observe] " . __("Incoming validation is in our database."));

                        $process->retrieve();
                    }
                });

                $session->register(
                    'com.ltlcapstone.validation',
                    function ($res) use ($session) {
                        $token = $res[0];

                        \Log::info($this->checkToken($token));
                        $dataUri = $res[1];
                        $settings = $res[2];

                        return $this->exampleValidationLaunch($dataUri, $settings);
                    }
                );
        }, false);

        Log::info(__("Subscription exited."));
    }

    public function exampleValidationLaunch($dataUri, $settings)
    {
        Log::info(__("Validation requested of ") . $dataUri);

        Log::info('Requesting data from ' . $dataUri);

        $data = app()->make(DataResource::class);
        $data->name = $dataUri;
        $data->settings = $settings;
        $data->url = $dataUri;

        $client = new GuzzleHttp\Client();
        $request = new GuzzleHttp\Psr7\Request('GET', $data->url);

        $promise = $client->sendAsync($request)->then(function ($response) use ($data) {
            $path = basename($data->url);
            $dData = $response->getBody();

            $data->filename = $path;
            $data->name = $path;
            $data->filetype = $data->settings['fileType'];
            $data->content = $dData;
            $data->save();

            return ValidationProcess::launch($data);
        }, function ($error) {
            abort(400, __("Invalid data URI request"));
        });

        $runs = $promise->wait();

        return $runs->first()->id;
    }

    /**
     * @author      Alex Bilbie <hello@alexbilbie.com>
     * @copyright   Copyright (c) Alex Bilbie
     * @license     http://mit-license.org/
     *
     * @link        https://github.com/thephpleague/oauth2-server
     */
    public function checkToken($jwt)
    {
        $resourceServer = app(\League\OAuth2\Server\ResourceServer::class);
        $tokenRepository = app(AccessTokenRepository::class);

        $cryptKey = new CryptKey(
            'file://'.Passport::keyPath('oauth-private.key'),
            null,
            false
        );

            $token = (new Parser())->parse($jwt);
        \Log::info('1');
        try {
            // Attempt to parse and validate the JWT
            $token = (new Parser())->parse($jwt);
        \Log::info('6');
            if ($token->verify(new Sha256(), $cryptKey->getKeyPath()) === false) {
                throw OAuthServerException::accessDenied('Access token could not be verified');
            }

        \Log::info('5');
            // Ensure access token hasn't expired
            $data = new ValidationData();
            $data->setCurrentTime(time());

        \Log::info('4');
            if ($token->validate($data) === false) {
                throw OAuthServerException::accessDenied('Access token is invalid');
            }

        \Log::info('3');
            // Check if token has been revoked
            if ($tokenRepository->isAccessTokenRevoked($token->getClaim('jti'))) {
                throw OAuthServerException::accessDenied('Access token has been revoked');
            }

        \Log::info('2');
            // Return the request with additional attributes
            return $request
                ->withAttribute('oauth_access_token_id', $token->getClaim('jti'))
                ->withAttribute('oauth_client_id', $token->getClaim('aud'))
                ->withAttribute('oauth_user_id', $token->getClaim('sub'))
                ->withAttribute('oauth_scopes', $token->getClaim('scopes'));
        } catch (\InvalidArgumentException $exception) {
            // JWT couldn't be parsed so return the request as is
            throw OAuthServerException::accessDenied($exception->getMessage());
        } catch (\RuntimeException $exception) {
            //JWR couldn't be parsed so return the request as is
            throw OAuthServerException::accessDenied('Error while decoding to JSON');
        }
    }
}
