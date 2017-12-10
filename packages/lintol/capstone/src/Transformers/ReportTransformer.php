<?php

namespace Lintol\Capstone\Transformers;

use League\Fractal;
use Lintol\Capstone\Models\Report;

class ReportTransformer extends Fractal\TransformerAbstract
{
    public function transform(Report $report)
    {
        return [
            'id' => $report->id,
            'name' => $report->name,
            'errors' => $report->errors,
            'warnings' => $report->warnings,
            'passes' => $report->passes,
            'qualityScore' => $report->quality_score,
            'content' => $report->content
        ];
    }
}
