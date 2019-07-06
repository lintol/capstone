<?php

namespace Lintol\Capstone\Transformers;

use App;
use Exception;
use League\Fractal;
use Lintol\Capstone\Models\Profile;

class ProfileTransformer extends Transformer
{
    public static function inputMapping()
    {
        return [
            'name' => 'name',
            'description' => 'description',
            'version' => 'version',
            'uniqueTag' => 'unique_tag',
            'rules' => 'rules'
        ];
    }

    public static function sideMapping()
    {
        return [
            'configurations' => 'configurations'
        ];
    }

    protected static $model = 'profiles';

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
            'rules' => $profile->rules,
            'created_at' => $profile->created_at,
            'updated_at' => $profile->updated_at,
            'creatorId' => $profile->creator_id,
            'uniqueTag' => $profile->unique_tag
        ];
    }

    public function includeConfigurations(Profile $profile)
    {
        return $this->collection(
            $profile->configurations,
            new ProcessorConfigurationTransformer,
            'processorConfigurations'
        );
    }
}
