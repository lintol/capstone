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

        return fractal($data, $this->transformer)
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
            'name' => $data->name
        ];
        $data->source_uri = $request->input('uri');

        $client = new GuzzleHttp\Client();
        $request = new GuzzleHttp\Psr7\Request('GET', $data->source_uri);
        \Log::info($data);

        $promise = $client->sendAsync($request)->then(function ($response) use ($data) {
            $path = basename($data->source_uri);
            $dData = $response->getBody();

            $data->filename = $path;
            $data->name = $path;
            $data->format = substr($path, strpos('.', $path) - strlen($path) + 1, 3);
            $settings = $data->settings;
            $settings['fileType'] = $data->format;
            $data->settings = $settings;
            $data->content = $dData;
            $data->save();
            \Log::info($data);

            ValidationProcess::launch($data);
        }, function ($error) {
            abort(400, __("Invalid data URI request"));
        });

        $promise->wait();

        if ($data->save()) {
            return fractal($data, $this->transformer)
                ->respond();
        }

        abort(400, __("Invalid data"));
    }
}
