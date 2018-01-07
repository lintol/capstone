<?php

namespace Lintol\Capstone\Services\Rules;

class NameMatch
{
    public function apply(array $metadata, array $rules)
    {
        if (in_array('name', $rules)) {
            if (in_array('name', $metadata)) {
                return (bool)preg_match($rules['name'], $metadata['name']);
            }
            return false;
        }

        return true;
    }
}
