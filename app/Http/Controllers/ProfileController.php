<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Lintol\Capstone\Models\Profile;
use Lintol\Capstone\Transformers\ProfileTransformer;

class ProfileController extends Controller
{
    /**
     * Initialize the transformer
     */
    public function __construct(ProfileTransformer $transformer)
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
        return Profile::all();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        return view('profiles.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $profile = new Profile;
        $profile->name = $request->input('name');
        $profile->version = $request->input('version');
        $profile->creator_id = $request->input('creatorId');
        $profile->description = $request->input('description');
        $profile->version = $request->input('version');
        $profile->unique_tag = $request->input('uniqueTag');

        if (!$profile->save()) {
            throw new HttpException(400, "Invalid data");
        }

        return fractal($profile, $this->transformer)
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
        //
        if (!$id) {
           throw new HttpException(400, "Invalid id");
        }
        return Profile::find($id);
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
        /*if (!$id) {
            throw new HttpException(400, "Invalid id");
        }*/
        $profile = Profile::find($id);
        $profile->name = $request->input('name');
        $profile->description = $request->input('description');

        if ($profile->save()) {
            return $profile;
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
