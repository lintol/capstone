<?php

namespace Lintol\Capstone;

use Carbon\Carbon;
use Auth;
use Lintol\Capstone\ResourceManager;
use Socialite;
use Silex\ckan\CkanClient;
use Illuminate\Support\Collection;
use Lintol\Capstone\Models\CkanInstance;
use Lintol\Capstone\Models\DataResource;
use App\RemoteUser;
use App\User;
use Hash;

class CkanResourceProvider implements ResourceProviderInterface
{
    protected $driver;

    private $remoteUser;

    private $ckanClient;

    protected $ckanInstance;

    public function __construct(ResourceManager $resourceManager)
    {
        /* This should be the only way of setting user credentials */
        $user = Auth::user();
        if ($user && $user->primaryRemoteUser && $user->primaryRemoteUser->driver === 'ckan') {
            $this->remoteUser = $user->primaryRemoteUser;
            $this->driver = $resourceManager->getOAuthDriver('ckan', $this->remoteUser->resourceable);
        } else {
            throw RuntimeException(__(
                "Attempt to create a CKAN resource provider " .
                "without CKAN credentials on logged in user"
            ));
        }
    }

    public static function generate(CkanInstance $ckanInstance)
    {
        $resourceProvider = app(self::class);

        $resourceProvider->setCkanInstance($ckanInstance);

        return $resourceProvider;
    }

    public function loadApiKey()
    {
        if (!$this->ckanClient) {
            if ($this->remoteUser && $this->remoteUser->remote_token) {
                $apiKey = $this->driver->getApiKeyByToken($this->remoteUser->remote_token);
            }

            if (!$apiKey) {
                throw RuntimeException(__(
                    "This resource provider did not successfully" .
                    " retrieve an API key from credentials."
                ));
            }

            $this->ckanClient = CkanClient::factory([
                'baseUrl' => $this->ckanInstance->uri . '/api',
                'apiKey' => $apiKey
            ]);
        }
    }

    public function setCkanInstance($ckanInstance)
    {
        $this->ckanInstance = $ckanInstance;
    }

    public function getDataResources($search = '', $filters = [], $sortBy = 'name', $orderDesc = false) : Collection
    {
        $this->loadApiKey();

        $user = Auth::user();
        $localData = $this->ckanInstance->resources()->whereUserId($user->id)->get()->keyBy('remote_id');

        $query = ['url' => '.'];
        if ($search) {
            $query['url'] = preg_replace('[^A-Za-z0-9_-.]', '', $search);
        }
        $allowedFormats = ['csv', 'json', 'geojson', 'xml'];
        if (array_key_exists('filetype', $filters) && in_array($filters['filetype'], $allowedFormats)) {
            $query['format'] = $filters['filetype'];
        }

        $ckanQuery = [];
        foreach ($query as $key => $value) {
            $ckanQuery[] = $key . ':' . $value;
        }
        $ckanQuery = ['query' => implode('&', $ckanQuery)];

        if ($sortBy) {
            switch ($sortBy) {
                case 'filename':
                    $ckanQuery['sort'] = 'url';
                    break;
                case 'name':
                    $ckanQuery['sort'] = 'name';
                    break;
                default:
                    $sortBy = null;
            }
            if ($sortBy) {
                if ($orderDesc) {
                    $ckanQuery['sort'] .= ' dec';
                } else {
                    $ckanQuery['sort'] .= ' asc';
                }
            }
        }

        for ($i = 0 ; $i < 10 ; $i++) {
            try {
                $search = $this->ckanClient->ResourceSearch($ckanQuery);
                break;
            } catch (\GuzzleHttp\\Exception\\ServerException $e) {
                if ($e->getResponse()->getStatusCode() != 502) {
                    throw $e;
                }
            }
        }

        if ($search) {
            $ckanData = collect($search['result']['results'])
                ->map(function ($ckanData) use ($localData, $user) {
                    $data = $localData->get($ckanData['id']);

                    if (!$data) {
                        $data = new DataResource;
                        $data->url = $ckanData['url'];
                        $data->remote_id = $ckanData['id'];
                        $data->source = 'CKAN';
                        $data->filetype = 'csv';
                        $data->archived = 0;
                        $data->filename = basename($data->url);
                        $data->status = 'valid link';
                        $data->resourceable = $this->ckanInstance;
                        $data->created_at = $ckanData['created'] ? Carbon::parse($ckanData['created']) : null;
                        $data->updated_at = $ckanData['last_modified'] ? Carbon::parse($ckanData['last_modified']) : null;
                    }

                    return $data;
                });
        } else {
            $ckanData = collect();
        }

        \Log::info($ckanData);
        return $ckanData;
    }

    public function getDataResource($id)
    {
        $this->loadApiKey();

        $user = Auth::user();
        $data = $this->ckanInstance->resources()->whereUserId($user->id)->whereRemoteId($id)->first();

        if (!$data) {
            $data = new DataResource;
        }

        $ckanData = $this->ckanClient->ResourceShow(['id' => $id]);

        if ($ckanData) {
            $ckanData = $ckanData['result'];
            $data->remote_id = $ckanData['id'];
            $data->url = $ckanData['url'];
            $data->user_id = $user->id;
            $data->source = 'CKAN';
            $data->filetype = 'csv';
            $data->archived = 0;
            $data->filename = basename($data->url);
            $data->status = 'valid link';
            $data->resourceable()->associate($this->ckanInstance);

            return $data;
        }

        return null;
    }

    public function getUsers() : Collection
    {
        $this->loadApiKey();

        /* TODO: cover clashing hashes case */
        $localUsers = User::leftJoin('remote_users', 'users.primary_remote_user_id', '=', 'remote_users.id')
            ->where('remote_users.driver', '=', 'ckan')
            ->whereNotNull('remote_users.remote_id_hash')
            ->get()->keyBy('remote_id_hash');

        $hashKey = config('capstone.encryption.blind-index-key');
        $ckanUsers = collect($this->ckanClient->GetUsers()->get('result'))
            ->map(function ($ckanUser) use ($localUsers, $hashKey) {
                $hash = hash_hmac('sha256', $ckanUser['id'], $hashKey);

                $user = $localUsers->get($hash);

                if (!$user) {
                    $remoteUser = new RemoteUser;
                    $remoteUser->driver = 'ckan';
                    $user = new User;
                    $user->primaryRemoteUser = $remoteUser;
                    $user->id = 'remote-' . $ckanUser['id'];
                }

                $user->primaryRemoteUser->remoteEmail = $ckanUser['email'];

                return $user;
            });


        return $ckanUsers;
    }
}
