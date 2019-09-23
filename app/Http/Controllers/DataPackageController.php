<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Lintol\Capstone\Models\DataPackage;
use Lintol\Capstone\Transformers\DataPackageTransformer;
use Carbon\Carbon;

class DataPackageController extends Controller
{
    /**
     * Initialize the transformer
     */
    public function __construct(DataPackageTransformer $transformer)
    {
        $this->transformer = $transformer;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $profile = DataPackage::findOrFail($id);

        return fractal()
            ->item($profile, $this->transformer, 'packages')
            ->respond();
    }
}
