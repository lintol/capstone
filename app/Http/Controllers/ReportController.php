<?php

namespace App\Http\Controllers;

use App\User;
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
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $reports = Report::all();

        $users = User::whereIn('id', $reports->pluck('owner_id')->filter())
          ->get()
          ->each(function (&$user) {
            $user->retrieve();
          })
          ->keyBy('id');

        $reports->each(function (&$report) use ($users) {
            if ($report->owner_id) {
                $report->owner = $users[$report->owner_id];
            }
        });

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
