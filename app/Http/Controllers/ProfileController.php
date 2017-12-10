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
        $profiles = Profile::all();

        return fractal($profiles, $this->transformer)
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
        $profile = new Profile;

        $profile->name = $request->input('name');
        $profile->version = $request->input('version');
        $profile->creator_id = $request->input('creatorId');
        $profile->description = $request->input('description');
        $profile->version = $request->input('version');
        $profile->unique_tag = $request->input('uniqueTag');

        if (!$profile->save()) {
            abort(400, "Invalid data");
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
        $profile = Profile::findOrFail($id);

        return fractal($profile, $this->transformer)
            ->respond();
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
        $profile = Profile::findOrFail($id);
        $profile->name = $request->input('name');
        $profile->description = $request->input('description');

        if ($profile->save()) {
            return fractal($profile, $this->transformer)
                ->respond();
        }

        abort(400, __("Invalid data"));
    }
}
