<?php

namespace App\Http\Controllers;

use GuzzleHttp;
use Illuminate\Http\Request;
use Lintol\Capstone\ValidationProcess;
use Lintol\Capstone\Models\Data;
use Lintol\Capstone\Transformers\DataTransformer;

class DataController extends Controller
{
    /**
     * Initialize the transformer
     */
    public function __construct(DataTransformer $transformer)
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
        $data = Data::all();

        return fractal()
            ->collection($data, $this->transformer, 'dataResources')
            ->respond();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = app()->make(Data::class);
        $data->settings = [
          'name' => $request->input('uri'),
          'dataProfileId' => $request->input('profileId')
        ];
        $data->source_uri = $request->input('uri');

        $client = new GuzzleHttp\Client();
        $request = new GuzzleHttp\Psr7\Request('GET', $data->source_uri);

        $promise = $client->sendAsync($request)->then(function ($response) use ($data) {
            $path = basename($data->source_uri);
            $dData = $response->getBody();

            $data->filename = $path;
            $data->name = $path;
            $pathParts = pathinfo($path);
            $data->format = $pathParts['extension'];
            $settings = $data->settings;
            $settings['fileType'] = $data->format;
            $data->settings = $settings;
            $data->content = $dData;
            $data->save();

            ValidationProcess::launch($data);
        }, function ($error) {
            abort(400, __("Invalid data URI request"));
        });

        $promise->wait();

        if ($data->save()) {
            return fractal()
                ->item($data, $this->transformer, 'dataResources')
                ->respond();
        }

        abort(400, __("Invalid data"));
    }
}
