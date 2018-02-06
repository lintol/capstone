<?php

namespace App\Http\Controllers;

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

        if ($request->input('configurations')) {
          foreach ($request->input('configurations') as $configurationObj) {
            $configuration = new ProcessorConfiguration;
            $configuration->processor_id = $configurationObj['attributes']['processor']['id'];
            $configuration->profile_id = $profile->id;

            // TODO: return to observer
            $configuration->rules = $configuration->processor->rules;
            $configuration->definition = $configuration->processor->definition;

            $configuration->save();
            \Log::info('sent');
          }
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
        $profile->name = $request->input('name');
        $profile->description = $request->input('description');

        if ($profile->save()) {
            return fractal()
                ->item($profile, $this->transformer, 'profiles')
                ->respond();
        }

        abort(400, __("Invalid data"));
    }
}
