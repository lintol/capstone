<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use Lintol\Capstone\Models\ProcessorConfiguration;
use Lintol\Capstone\Models\Profile;
use Lintol\Capstone\Transformers\ProfileTransformer;
use Lintol\Capstone\Observer\ProcessorConfigurationObserver;

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

        return fractal()
            ->collection($profiles, $this->transformer, 'profiles')
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
        // $this->authorize('create', Profile::class);

        $input = $request->json()->all();

        $profile = $this->transformer->parse($input);
        $profile->configurations->each(function ($configuration) {
          $configuration->updateDefinition();
        });

        DB::beginTransaction();

        try {
            if (!$profile->save()) {
                abort(400, "Invalid profile data");
            }

            if (!$profile->configurations()->saveMany($profile->configurations)) {
                abort(400, "Invalid configuration data");
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollback();
            throw $e;
        }

        return fractal()
            ->item($profile, $this->transformer, 'profiles')
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

        return fractal()
            ->item($profile, $this->transformer, 'profiles')
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

        $input = $request->json()->all();

        $profile = $this->transformer->parse($input, $profile);

        $profile->configurations->each(function ($configuration) {
            $configuration->updateDefinition();
        });

        DB::beginTransaction();

        try {
            if (!$profile->save()) {
                abort(400, "Invalid profile data");
            }

            if (!$profile->configurations()->saveMany($profile->configurations)) {
                abort(400, "Invalid configuration data");
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollback();
            throw $e;
        }

        return fractal()
            ->item($profile, $this->transformer, 'profiles')
            ->respond();
    }
}
