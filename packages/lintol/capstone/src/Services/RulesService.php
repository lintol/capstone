<?php

namespace Lintol\Capstone\Services;

use Lintol\Capstone\Services\RulesEngine;
use Lintol\Capstone\Models\Rule;
use Lintol\Capstone\Models\Data;
use Lintol\Capstone\Models\Profile;

class RulesService
{
    protected $engine;

    public function __construct(RulesEngine $engine)
    {
        $this->engine = $engine;
    }

    public function filter($definition, $rules)
    {
        return $this->engine->apply($definition, $rules);
    }

    public function match($definition, $profiles)
    {
        return $profiles
            ->map(function ($profile) use ($definition) {
                if ($this->filter($definition, $profile->rules)) {
                    return $profile;
                }
                return null;
            })
            ->filter()
            ->pluck('id');
    }
}
