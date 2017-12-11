<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Lintol\Capstone\Models\Report;
use Lintol\Capstone\Transformers\ReportTransformer;

class ReportController extends Controller
{
    /**
     * Initialize the transformer
     */
    public function __construct(ReportTransformer $transformer)
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
        $reports = Report::all(); 

        return fractal($reports, $this->transformer)
            ->respond();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $report = Report::findOrFail($id);

        return fractal($report, $this->transformer)
            ->respond();
    }
}
