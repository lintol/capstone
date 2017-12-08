<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReportLogLineController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return ReportLogLine::all();
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
        //
        $reportLogLine = new ReportLogLine;
        $reportLogLine->ragType = $request->input('ragType');
        $reportLogLine->message = $request->input('message');
        $reportLogLine->processor = $request->input('processor');
        $reportLogLine->detail = $request->input('detail');
        $reportLogLine->created_at = date('Y-m-d H:i:s');
        $reportLogLine->updated_at = date('Y-m-d H:i:s');
        if ($reportLogLine->save()) {
            return $reportLogLine;
        }
        throw new HttpException(400, "Invalid data ");

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
