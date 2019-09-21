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
            'profile' => $report->run && $report->run->profile ? $report->run->profile->name : $report->profile,
            'errors' => $report->errors,
            'warnings' => $report->warnings,
            'passes' => $report->passes,
            'qualityScore' => $report->quality_score,
            'content' => $report->content,
            'createdAt' => $report->created_at,
            'profileId' => $report->getProfileId(),
            'dataResourceId' => $report->getDataResourceId()
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

    public function includeDataResource(Report $report)
    {
        if ($report->data_resource) {
            return $this->item(
                $report->data_resource,
                new DataResourceTransformer,
                'resources'
            );
        }

        return null;
    }
}
