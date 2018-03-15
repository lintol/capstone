<?php

namespace Lintol\Capstone\Transformers;

use App\Transformers\UserTransformer;
use League\Fractal;
use Lintol\Capstone\Models\Report;

class ReportTransformer extends Fractal\TransformerAbstract
{
    protected $defaultIncludes = [
        'user'
    ];

    public function transform(Report $report)
    {
        return [
            'id' => $report->id,
            'name' => $report->name,
            'profile' => $report->run ? $report->run->profile->name : $report->profile,
            'errors' => $report->errors,
            'warnings' => $report->warnings,
            'passes' => $report->passes,
            'qualityScore' => $report->quality_score,
            'content' => $report->content,
            'createdAt' => $report->created_at
        ];
    }

    public function includeUser(Report $report)
    {
        if ($report->owner) {
            return $this->item(
                $report->owner,
                new UserTransformer,
                'users'
            );
        }

        return null;
    }
}
