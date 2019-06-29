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

$group = [
    'prefix' => 'v1.0'
];

Route::group($group, function () {
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
});
