<?php

namespace Lintol\Capstone\Services\Rules;

class SizeMatch
{
    public function apply(array $metadata, array $rules)
    {
        if (array_key_exists('maxSize', $rules)) {
            return true; // FIXME: not correctly filtering
            if (array_key_exists('size', $metadata)) {
                return $metadata['size'] && $metadata['size'] < $rules['maxSize'];
            }
            return false;
        }

        return true;
    }
}
