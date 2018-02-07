<?php

namespace App\Http\Controllers;

/**
 * From lintol/bedappy-controllers
 */

use App;
use Illuminate\Http\Request;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Waavi\Sanitizer\Sanitizer;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Sanitize a dataset using given filters.
     *
     * @param array $data
     * @param array $filters
     * @return array
     */
    public function sanitize($request, $filters)
    {
        $data = $this->sanitizeData($request->all(), $filters);

        $request->replace($data);

        return $request;
    }

    /**
     * Get a sanitizer
     *
     * @return Waavi\Sanitizer\Sanitizer
     */
    public function sanitizeData($data, $filters)
    {
        $factory = App::make('sanitizer');

        $sanitizer = $factory->make($data, $filters);

        return $sanitizer->sanitize();
    }

    /**
     * Check whether a value is a UUID
     *
     * @param Illuminate\Http\Request $request
     * @param string $uuid
     * @param string $message
     * @return void
     */
    public function validateUuid(Request $request, $uuid, $message = "Invalid UUID")
    {
        $validator = $this->getValidationFactory()->make(
            ['uuid' => $uuid],
            ['uuid' => 'required|uuid'],
            ['uuid' => $message]
        );

        if ($validator->fails()) {
            $this->throwValidationException($request, $validator);
        }
    }
}
