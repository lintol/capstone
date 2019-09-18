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

$machineGroup = [
    'prefix' => 'v1.0'
];

if (!env('CAPSTONE_WITHOUT_AUTH', false)) {
    $group['middleware'] = 'auth:api';
    $machineGroup['middleware'] = 'client';
}

//Route::get('reports/all', 'ReportController@all');

//Route::group($machineGroup, function () {
//    Route::resource('dataResources', 'DataResourceController', [
//        'only' => ['all']
//    ]);
//    Route::resource('reports', 'ReportController', [
//        'only' => ['all']
//    ]);
//});

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
    Route::post('reports/{id}/rerun', 'ReportController@rerun');
    Route::resource('dataResources', 'DataResourceController', [
        'only' => ['index', 'show', 'store', 'update', 'destroy']
    ]);

    Route::get('dataResources/getFileTypeFilters', 'DataResourceController@getFileTypeFilters')->name('getFileTypeFilters');
    Route::get('dataResources/getSourceFilters', 'DataResourceController@getSourceFilters')->name('getSourceFilters');
    Route::get('dataResources/getDateFilters', 'DataResourceController@getDateFilters')->name('getDateFilters');
    Route::get('dataResources/summary', 'DataResourceController@summary');

    Route::get('statistics/entities', 'StatisticsController@entities');

    Route::get('tracking', function () {
        return \App\StatusTracking::where('created_at', '>', \Carbon\Carbon::now()->subHours(24))->get();
    });

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
