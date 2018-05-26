<?php

namespace App\Http\Controllers\Auth;

use Auth;
use App\User;
use Lintol\Capstone\Models\CkanInstance;
use Lintol\Capstone\ResourceManager;
use Hash;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Socialite;
use Crypt;
use Laravel\Socialite\Two\CkanProvider;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @return string
     */
    protected function redirectPath()
    {
        $url = config('capstone.frontend.url', '/');

        return $url;
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    protected $socialiteDriver = [
        'ckan' => null,
        'github' => 'https://github.com'
    ];

    /**
     * Redirect the user to the OAuth2 authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToProvider($driverName)
    {
        if (!$this->checkDriverActive($driverName)) {
            abort(400, __("No OAuth2 available for this provider"));
        }

        if ($driverName == 'ckan') {
            $driverServer = request()->input('server');
            $validServers = config('capstone.authentication.ckan.valid-servers');

            if (!empty($driverServer) && in_array($driverServer, $validServers)) {
                $resourceable = CkanInstance::whereUri($driverServer)->first();
                if (!$resourceable) {
                    $resourceable = CkanInstance::create([
                        'name' => 'CKAN:' . $driverServer,
                        'uri' => $driverServer
                    ]);
                }

                $driver = app(ResourceManager::class)->getOAuthDriver($driverName, $resourceable);
            } else {
                abort(400, __("You must provide a valid CKAN server to authenticate against."));
            }
        } else {
            $driver = Socialite::driver($driverName);
        }

        return $driver->redirect();
    }

    /**
     * Check whether this driver is active
     *
     * @return bool
     */
    public function checkDriverActive($driver)
    {
        if (!array_key_exists($driver, $this->socialiteDriver)) {
            return false;
        }

        if ($driver == 'github') {
            return config('capstone.features.services-github', false);
        }

        // If any other driver has not been disabled and is included in the list...
        return true;
    }

    /**
     * Obtain the user information from OAuth2.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleProviderCallback($driver, $remoteServerId = null)
    {
        if (!$this->checkDriverActive($driver)) {
            abort(400, __("No OAuth2 available for this provider"));
        }

        $driverServer = $this->socialiteDriver[$driver];

        $resourceable = null;

        switch ($driver) {
            case 'ckan':
              if (!$remoteServerId) {
                  abort(400, __("No valid OAuth2 server known or provided."));
              }
              $resourceable = CkanInstance::findOrFail($remoteServerId);
              $driverServer = $resourceable->uri;
              break;

            case 'github':
              $resourceable = null;
              break;

            default:
              abort(400, __("No valid OAuth2 server known or provided."));
        }

        $oauthUser = app(ResourceManager::class)->getOAuthDriver($driver, $resourceable)->user();

        $user = User::findByRemote($driver, $driverServer, $oauthUser, true);

        $user->saveRemote($oauthUser);

        $remoteUser = $user->primaryRemoteUser;

        if (!$remoteUser->resourceable && $resourceable) {
            $remoteUser->resourceable()->associate($resourceable);
            $remoteUser->save();
        }

        Auth::login($user, true);

        return redirect($this->redirectPath());
    }

    /**
     * Logout the current user.
     *
     * @return \Illuminate\Http\Response
     */
    public function logout()
    {
        $user = Auth::user();

        if ($user && $user->primaryRemoteUser) {
            $user->primaryRemoteUser->remote_token = null;
            $user->primaryRemoteUser->save();
        }

        Auth::logout();

        return redirect($this->redirectPath());
    }
}
