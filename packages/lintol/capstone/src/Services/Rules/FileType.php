<?php

namespace Lintol\Capstone\Services\Rules;

class FileType
{
    public function apply(array $metadata, array $rules)
    {
        if (array_key_exists('fileType', $rules)) {
            if (array_key_exists('fileType', $metadata)) {
                return (bool)preg_match($rules['fileType'], $metadata['fileType']);
            }
            return false;
        }

        return true;
    }
}
