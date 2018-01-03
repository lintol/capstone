<?php

namespace Lintol\Capstone\Services;

use Lintol\Capstone\Services\Rules\FileType;

class RulesEngine
{
    public $rules = [
        FileType::class
    ];

    public function apply(array $rules)
    {
        foreach (self::$rules as $ruleClass) {
            $rule = app()->make($ruleClass);
            $rule->apply($metadata, $rules);
        }
    }
}
