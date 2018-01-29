<?php

namespace Lintol\Capstone\Transformers;

use League\Fractal;
use Lintol\Capstone\Models\Profile;

class ProfileTransformer extends Fractal\TransformerAbstract
{
    protected $defaultIncludes = [
        'configurations'
    ];

    public function transform(Profile $profile)
    {
        return [
            'id' => $profile->id,
            'name' => $profile->name,
            'description' => $profile->description,
            'version' => $profile->version,
            'creatorId' => $profile->creator_id,
            'uniqueTag' => $profile->unique_tag
        ];
    }

    public function includeConfigurations(Profile $profile)
    {
        return $this->collection(
            $profile->configurations,
            new ProcessorConfigurationTransformer
        );
    }
}
