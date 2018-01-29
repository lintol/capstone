<?php

namespace Lintol\Capstone\Services\Rules;

class DataProfileIdMatch
{
    public function apply(array $metadata, array $rules)
    {
        if (array_key_exists('dataProfileId', $metadata)) {
            if (array_key_exists('dataProfileId', $rules)) {
                $result = $rules['dataProfileId'] == $metadata['dataProfileId'];
                return $result;
            }
            return true;
        }

        return true;
    }
}
