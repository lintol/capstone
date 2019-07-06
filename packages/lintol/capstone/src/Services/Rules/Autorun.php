<?php

namespace Lintol\Capstone\Services\Rules;

class Autorun
{
    public function apply(array $metadata, array $rules)
    {
        if (array_key_exists('autorun', $rules)) {
            if (array_key_exists('autorun', $metadata)) {
                return $rules['autorun'] == $metadata['autorun'];
            }
            return false;
        }

        return true;
    }
}
