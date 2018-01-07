<?php

namespace Lintol\Capstone\Services;

use Lintol\Capstone\Services\Rules\FileType;
use Lintol\Capstone\Services\Rules\NameMatch;

class RulesEngine
{
    public static $rules = [
        FileType::class,
        NameMatch::class
    ];

    public $ruleObjs = [];

    public function __construct()
    {
        $ruleObjs = [];
        foreach (self::$rules as $rule) {
            $ruleObjs[$rule] = app()->make($rule);
        }
    }

    public function apply(array $definition, array $rules)
    {
        foreach ($this->ruleObjs as $rule) {
            if (!$rule->apply($definition, $rules)) {
                return false;
            }
        }

        return true;
    }
}
