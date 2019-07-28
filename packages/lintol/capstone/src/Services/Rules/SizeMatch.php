<?php

namespace Lintol\Capstone\Services\Rules;

class SizeMatch
{
    public function apply(array $metadata, array $rules)
    {
        if (array_key_exists('maxSize', $rules)) {
            if (array_key_exists('size', $metadata)) {
                return $metadata['size'] && $metadata['size'] < $rules['maxSize'];
            }
            return false;
        }

        return true;
    }
}
