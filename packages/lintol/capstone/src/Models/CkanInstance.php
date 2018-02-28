<?php

namespace Lintol\Capstone\Models;

use Alsofronie\Uuid\UuidModelTrait;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;

class CkanInstance extends Authenticatable
{
    use UuidModelTrait, HasRoles, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'uri'
    ];
}
