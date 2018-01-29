<?php

namespace Lintol\Capstone\Services\Rules;

class NameMatch
{
    public function apply(array $metadata, array $rules)
    {
        if (array_key_exists('name', $rules)) {
            if (array_key_exists('name', $metadata)) {
                return (bool)preg_match($rules['name'], $metadata['name']);
            }
            return false;
        }

        return true;
    }
}
