<?php

namespace Lintol\Capstone\Transformers;

use League\Fractal;
use Lintol\Capstone\Models\DataPackage;
use App\Transformers\UserTransformer;

class DataPackageTransformer extends Fractal\TransformerAbstract
{
    protected $defaultIncludes = [
    ];

    public function transform(DataPackage $data)
    {
        return [
            'id' => ($data->id || !$data->remote_id) ? $data->id : 'remote-' . $data->remote_id,
            'metadata' => $data->metadata,
            'url' => $data->url,
            'source' => $data->source,
            'created_at' => $data->created_at,
            'locale' => $data->locale,
            'archived' => $data->archived
        ];
    }

    public function includeUser(DataPackage $data)
    {
        if ($data->user) {
            return $this->item(
                $data->user,
                new UserTransformer,
                'users'
            );
        }

        return null;
    }
}
