<?php

namespace Lintol\Capstone\Services;

use Lintol\Capstone\Services\Rules\FileType;
use Lintol\Capstone\Services\Rules\NameMatch;
use Lintol\Capstone\Services\Rules\DataProfileIdMatch;
use Lintol\Capstone\Services\Rules\Autorun;

class RulesEngine
{
    public static $rules = [
        FileType::class,
        NameMatch::class,
        DataProfileIdMatch::class,
        Autorun::class
    ];

    public $ruleObjs = [];

    public function __construct()
    {
        foreach (self::$rules as $rule) {
            $this->ruleObjs[$rule] = app()->make($rule);
        }
    }

    public function apply(array $definition, array $rules)
    {
        foreach ($this->ruleObjs as $rule) {
            if (!$rule->apply($definition, $rules)) {
                \Log::info(get_class($rule) . ' failed');
                return false;
            }
            \Log::info(get_class($rule) . ' passed');
        }

        return true;
    }
}
