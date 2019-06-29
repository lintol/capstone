<?php

namespace App\Http\Controllers;

use App\User;
use Carbon\Carbon;
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
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $reports = new Report;

        if ($request->input('since')) {
            $this->validate($request, ['since' => 'required|date']);

            $reports = $reports->whereDate(
                'created_at',
                '>=',
                Carbon::parse($request->input('since'))
            )->whereTime(
                'created_at',
                '>=',
                Carbon::parse($request->input('since'))
            );
        }

        $reports = $reports->get();

        $users = User::whereIn('id', $reports->pluck('owner_id')->filter())
          ->get()
          //->each(function (&$user) {
          //  $user->retrieve();
          //})
          ->keyBy('id');

        // $reports->each(function (&$report) use ($users) {
        //     if ($report->owner_id) {
        //         $report->owner = $users[$report->owner_id];
        //     }
        // });

        return fractal()
            ->collection($reports, $this->transformer, 'reports')
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

        if ($report->owner) {
            $report->owner->retrieve();
        }

        return fractal()
            ->item($report, $this->transformer, 'reports')
            ->respond();
    }
}
