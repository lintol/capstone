<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Processor;

class ProcessorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Processor::all(); 
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
        $processor = new Processor;

        $processor->name = $request->input('name');
        $processor->creator = $request->input('creator');
        $processor->description = $request->input('description');
        $processor->unique_Tag = $request->input('uniqueTag');

        if ($processor->save()) {
            return $processor;
        }

        throw new HttpException(400, "Invalid data");
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
        if (!$id) {
            throw new HttpException(400, "Invalid id");
        }
        $processor = Processor::find($id);
        $processor->name = $request->input('name');
        $processor->description = $request->input('description');
        $processor->updated_at = date('Y-m-d H:i:s');
        if ($processor->save()) {
            return $processor;
        }
        throw new HttpException(400, "Invalid data");
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
