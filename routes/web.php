<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('login', function () {
    if (Auth::guard('api')->user()) {
        return redirect('/');
    }

    return view('login');
})->name('login');

if (config('capstone.features.local-admin-login', false)) {
    Route::get('login/local-admin', 'Auth\LoginController@localAdminLogin')->name('login.local-admin');
}
Route::get('login/{driver}', 'Auth\LoginController@redirectToProvider')->name('login.by-driver');
Route::get('login/{driver}/callback', 'Auth\LoginController@handleProviderCallback');
Route::get('login/{driver}/callback/{id}', 'Auth\LoginController@handleProviderCallback');
Route::get('logout', 'Auth\LoginController@logout');

if (config('capstone.frontend.proxy', false)) {
    Route::get('{any?}', function ($any = null) {
        if (strpos($any, '__webpack_hmr') !== false) {
            abort(400);
        }

        if ($any == '') {
            $any = Config::get('capstone.frontend.prefix', 'static/');
        }

        $frontend = config('capstone.frontend.proxy', false);
        $client = new GuzzleHttp\Client();
        $url = $frontend . $any;

        $response = $client->request('GET', $url);
        $body = $response->getBody();
        $tokenString = '{{ csrf_token()                       }}';

        $body = str_replace($tokenString, csrf_token(), $body->getContents());

        return $response->withBody(GuzzleHttp\Psr7\stream_for($body));

    })->where('any', '.*');
} else {
    Route::get('/', function () { return view('index'); });
}
