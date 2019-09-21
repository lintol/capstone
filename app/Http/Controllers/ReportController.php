<?php

namespace App\Http\Controllers;

use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use Lintol\Capstone\Models\Report;
use Lintol\Capstone\Models\ValidationRun;
use Lintol\Capstone\Transformers\ReportTransformer;
use Lintol\Capstone\Jobs\ProcessDataJob;

class ReportController extends Controller
{
    protected $validSortBy = ['created_at'];

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
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $reports = new Report;

        if ($request->input('since')) {
            $this->validate($request, ['since' => 'required|date']);

            Log::debug("Filtering with since");
            $reports = $reports->where(
                'created_at',
                '>=',
                Carbon::parse($request->input('since'))
            );
        }

        if ($request->input('until')) {
            $this->validate($request, ['until' => 'required|date']);

            Log::debug("Filtering with until");
            $reports = $reports->where(
                'created_at',
                '<',
                Carbon::parse($request->input('until'))
            );
        }

        if ($request->input('entity') && $request->input('id')) {
            $this->validate($request, ['id' => 'uuid']);
            $entity = $request->input('entity');
            $entityId = $request->input('id');

            if ($entity == 'resource') {
                $runQuery = ValidationRun::whereDataResourceId($entityId);
            } else if ($entity == 'package') {
                $resourceIds = DataResource::wherePackageId($entityId)->pluck('id');
                $runQuery = ValidationRun::whereIn('data_resource_id', $resourceIds);
            } else if ($entity == 'profile') {
                $runQuery = ValidationRun::whereProfileId($entityId);
            } else {
                return response([
                    'success' => false,
                    'message' => 'Only resource and profile searches are allowed'
                ], 400);
            }
            $runIds = $runQuery->pluck('id');
            $reports = $reports->whereIn(
                'run_id',
                $runIds
            );
        }

        $sortBy = request()->input('sortBy');

        if (!in_array($sortBy, $this->validSortBy)) {
            $sortBy = 'created_at';
        }

        $orderDesc = ! (request()->input('order') == 'asc');
        $reports = $reports->orderBy($sortBy, $orderDesc ? 'desc' : 'asc');

        $maxPagination = config('capstone.frontend.max-pagination', 250);

        $count = (int) request()->input('count');
        if (!$count || $count > $maxPagination) {
            $count = $maxPagination;
        }

        $paginator = $reports->paginate($count);
        $reports = $paginator->getCollection();
        $paginator->setPath('/reports/');

        $response = fractal()
            ->collection($reports, $this->transformer, 'reports')
            ->paginateWith(new IlluminatePaginatorAdapter($paginator))
            ->respond();

        return $response;
    }

    /**
     * Display a listing of the resource for machine route.
     *
     * @return \Illuminate\Http\Response
     */
    public function all()
    {
        return $this->index();
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

        if ($report->owner) {
            $report->owner->retrieve();
        }

        return fractal()
            ->item($report, $this->transformer, 'reports')
            ->respond();
    }

    /**
     * Re-run the specified validation run.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function rerun($id)
    {
        $report = Report::findOrFail($id);

        $run = $report->run;
        if (! $run) {
            throw RuntimeException(__("This report has no matching validation run."));
        }

        $newRun = $run->duplicate();
        $newRun->save();

        ProcessDataJob::dispatch($newRun->id);

        return $newRun->id;
    }
}
