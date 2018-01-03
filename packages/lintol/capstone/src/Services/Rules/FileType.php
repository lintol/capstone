<?php

namespace Lintol\Capstone\Services\Rules;

class FileType
{
    public function apply(array $metadata, array $rules)
    {
        if (in_array('fileType', $metadata) && in_array('fileType', $rules)) {
            return (bool)preg_match($rules['fileType'], $metadata);
        }
    }
}
