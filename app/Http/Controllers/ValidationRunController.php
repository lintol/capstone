<?php

namespace App\Http\Controllers;

use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use Lintol\Capstone\Models\ValidationRun;
use Lintol\Capstone\Jobs\ProcessDataJob;

class ValidationRunController extends Controller
{
    protected $validSortBy = ['created_at'];

    /**
     * Initialize the transformer
     */
    public function __construct()
    {
    }

    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function summary(Request $request)
    {
        $model = app()->make(ValidationRun::class);

        return [
            'success' => true,
            'statuses' => $model->summaryByStatus()
        ];
    }
}
