<?php

namespace Lintol\Capstone\Transformers;

use League\Fractal;
use Lintol\Capstone\Models\DataResource;
use App\Transformers\UserTransformer;

class DataResourceTransformer extends Fractal\TransformerAbstract
{
    protected $defaultIncludes = [
        'user'
    ];

    public function transform(DataResource $data)
    {
        return [
            'id' => ($data->id || !$data->remote_id) ? $data->id : 'remote-' . $data->remote_id,
            'filename' => $data->filename,
            'url' => $data->url,
            'filetype' => $data->filetype,
            'status' => $data->status,
            'source' => $data->source,
            'created_at' => $data->created_at,
            'archived' => $data->archived,
            'providerId' => $data->resourceable ? $data->resourceable->id : null,
            'providerType' => $data->resourceable ? $data->resourceable->driver : null,
            'providerServer' => $data->resourceable ? $data->resourceable->uri : null
        ];
    }

    public function includeUser(DataResource $data)
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
