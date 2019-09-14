<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Lintol\Capstone\Models\DataResource;
use Lintol\Capstone\Models\Report;
use Lintol\Capstone\Models\DataPackage;
use Lintol\Capstone\Models\DataResourceStatusChange;
use Lintol\Capstone\Models\ValidationRun;
use Carbon\Carbon;

class StatisticsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function entities()
    {
        $since = Carbon::now()->subDays(1);

        $entities = [
            DataResource::class,
            DataResourceStatusChange::class,
            DataPackage::class,
            Report::class,
            ValidationRun::class,
        ];

        $statistics = [];

        while ($since < Carbon::now()) {
            $until = $since->addHours(1);

            $statistics[$until->format('c')] = [];

            foreach ($entities as $entity) {
                $statistics[$until->format('c')][$entity] = app()->make($entity)->where('created_at', '<', $until)->count();
            }

            $since = $until;
        }

        return [
            'success' => true,
            'results' => $statistics
        ];
    }
}
