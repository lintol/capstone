<?php

namespace Lintol\Capstone;

use Auth;
use Lintol\Capstone\CkanResourceProvider;

class ResourceManager
{
    public function getProvider()
    {
        $currentUser = Auth::user();

        $remoteUser = $currentUser->primaryRemoteUser;

        $resourceProvider = null;

        if ($remoteUser) {
            $resourceable = $remoteUser->resourceable;

            if ($resourceable) {
                switch ($remoteUser->driver) {
                    case 'ckan':
                        $resourceProvider = CkanResourceProvider::generate($resourceable, $remoteUser);
                        break;
                    default:
                }
            }
        }

        return $resourceProvider;
    }
}
