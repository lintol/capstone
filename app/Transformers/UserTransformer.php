<?php

namespace App\Transformers;

use League\Fractal;
use App\User;

class UserTransformer extends Fractal\TransformerAbstract
{
    public function transform(User $user)
    {
        return [
          'id' => $user->id,
          'name' => $user->name,
          'nickname' => $user->nickname,
          'avatar' => $user->avatar,
          'email' => $user->email,
          'driver' => $user->driver,
          'driverServer' => $user->driver_server,
          'present' => $user->present
        ];
    }
}
