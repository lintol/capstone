<?php

namespace App\Http\Controllers;

use GuzzleHttp;
use Lintol\Capstone\Models\DataResource;
use Illuminate\Http\Request;
use Lintol\Capstone\ValidationProcess;
use Lintol\Capstone\Transformers\DataResourceTransformer;
use Illuminate\Support\Facades\Log;

class DataResourceController extends Controller
{

    public function __construct(DataResourceTransformer $transformer)
    {
        $this->transformer = $transformer;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $data = DataResource::paginate(25);
        $data->setPath('/dataResources/');
        
        
        return fractal()
            ->collection($data, $this->transformer, 'dataResources')
            ->respond();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $dataResource = app()->make(DataResource::class);
        $dataResource->settings = [
          'name' => $request->input('uri'),
          'dataProfileId' => $request->input('profileId')
        ];
        $dataResource->stored = $request->input('stored');
        $dataResource->url = $request->input('uri');
        $dataResource->filetype = $request->input('filetype');
        // $dataResource->user = $request->user;

        $client = new GuzzleHttp\Client();
        $request = new GuzzleHttp\Psr7\Request('GET', $dataResource->url);

        $promise = $client->sendAsync($request)->then(function ($response) use ($dataResource) {
            $path = basename($dataResource->url);
            $dData = $response->getBody();

            $dataResource->filename = $path;
            $dataResource->name = $path;
            $pathParts = pathinfo($path);
            $dataResource->filetype = $pathParts['extension'];
            $settings = $dataResource->settings;
            $settings['fileType'] = $dataResource->filetype;
            $dataResource->settings = $settings;
            $dataResource->content = $dData;
            $dataResource->save();

            ValidationProcess::launch($dataResource);
        }, function ($error) {
            abort(400, __("Invalid data URI request"));
        });

        $promise->wait();

        if ($dataResource->save()) {
            return fractal()
                ->item($dataResource, $this->transformer, 'dataResources')
                ->respond();
        }

        abort(400, __("Invalid data"));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\DataResource  $dataResource
     * @return \Illuminate\Http\Response
     */
    public function show(DataResource $dataResource)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\DataResource  $dataResource
     * @return \Illuminate\Http\Response
     */
    public function edit(DataResource $dataResource)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\DataResource  $dataResource
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, DataResource $dataResource)
    {
        //
        $dataResource2 = DataResource::findOrFail($dataResource->id);
        $dataResource2->archived = $dataResource->archived;
        $dataResource2->save();
     
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\DataResource  $dataResource
     * @return \Illuminate\Http\Response
     */
    public function destroy(DataResource $dataResource)
    {
        //
        $resource = DataResource::findOrFail($dataResource->id);
        $resource->delete();
        
    }
}
