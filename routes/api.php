<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

$group = [
    'prefix' => 'v1.0'
];

if (!env('CAPSTONE_WITHOUT_AUTH', false)) {
    $group['middleware'] = 'auth:api';
}

Route::group($group, function () {
    Route::get('users/me', 'UserController@me');

    Route::resource('profiles', 'ProfileController', [
        'only' => ['index', 'show', 'update', 'store']
    ]);
    Route::resource('processors', 'ProcessorController', [
        'only' => ['index', 'store']
    ]);
    Route::resource('reports', 'ReportController', [
        'only' => ['index', 'show']
    ]);
    Route::resource('dataResources', 'DataResourceController', [
        'only' => ['index', 'store', 'update', 'destroy']
    ]);
    Route::resource('dataResources/settings', 'DataResourceSettingController', [
        'only' => ['store']
    ]);
    /* Will enable when pagination in the front end is working 
    Route::get('dataResources', function () {
      return Lintol\Capstone\Models\DataResource::paginate();
    }); */
    Route::resource('users', 'UserController', [
        'only' => ['index']
    ]);
});
