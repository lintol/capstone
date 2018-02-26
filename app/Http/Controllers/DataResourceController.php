<?php

namespace App\Http\Controllers;

use Lintol\Capstone\Models\DataResource;
use Illuminate\Http\Request;
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
        $dataResource = new DataResource;
        $dataResource->filename = $request->filename;
        $dataResource->stored = $request->stored;
        $dataResource->url = $request->url;
        $dataResource->filetype = $request->filetype;
        $dataResource->user = $request->user;
        Log::info('');
     
        if ($dataResource->save()) {
            return fractal()
                ->item($dataResource,$this->transformer)
                ->respond();
        }
        
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
        $dataResource2 = Employee::findOrFail($dataResource->id);
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
    }
}
