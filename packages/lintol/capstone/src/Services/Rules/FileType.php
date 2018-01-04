<?php

namespace Lintol\Capstone\Services\Rules;

class FileType
{
    public function apply(array $metadata, array $rules)
    {
        if (in_array('fileType', $rules)) {
            if (in_array('fileType', $metadata)) {
                return (bool)preg_match($rules['fileType'], $metadata['fileType']);
            }
            return false;
        }

        return true;
    }
}
