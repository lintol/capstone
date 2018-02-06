<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Lintol\Capstone\Models\Processor;
use Lintol\Capstone\Transformers\ProcessorTransformer;

class ProcessorController extends Controller
{
    /**
     * Initialize the transformer
     */
    public function __construct(ProcessorTransformer $transformer)
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
        $processors = Processor::all();

        return fractal()
            ->collection($processors, $this->transformer, 'processors')
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
        $processor = new Processor;

        // TODO: incorporate bedappy sanitization pattern
        //
        $processor->name = $request->input('name');
        $processor->creator_id = $request->input('creatorId');
        $processor->description = $request->input('description');
        $processor->module = $request->input('module');
        $processor->content = $request->input('content');
        $processor->unique_tag = $request->input('uniqueTag');

        if ($processor->save()) {
            return fractal()
                ->item($processor, $this->transformer, 'processors')
                ->respond();
        }

        throw new HttpException(400, "Invalid data");
    }
}
