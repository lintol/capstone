<?php

namespace Lintol\Capstone;

use Illuminate\Support\Collection;

interface ResourceProviderInterface
{
    public function getUsers() : Collection;

    public function getDataResources() : Collection;
}
