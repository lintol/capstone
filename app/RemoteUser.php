<?php

namespace App;

use Hash;
use Socialite;
use Alsofronie\Uuid\UuidModelTrait;
use Illuminate\Database\Eloquent\Model;

class RemoteUser extends Model
{
    use UuidModelTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'remote_id_hash', 'driver', 'driver_server'
    ];

    public $remoteId = null;

    public $remoteNickname = null;

    public $remoteName = null;

    public $remoteEmail = null;

    public $remoteAvatar = null;

    /**
     * Retrieve details temporarily from OAuth provider
     *
     * @return bool success
     */
    public function retrieve()
    {
        if ($this->driver && $this->remote_token) {
            $remoteUser = Socialite::driver($this->driver)->userFromToken($this->remote_token);

            if ($remoteUser) {
                $this->remoteId = $remoteUser->getId();
                $this->remoteNickname = $remoteUser->getNickname();
                $this->remoteAvatar = $remoteUser->getAvatar();
                $this->remoteName = $remoteUser->getName();
                $this->remoteEmail = $remoteUser->getEmail();
            }
        }
    }

    public function updateFromSocialite($socialiteUser)
    {
        $hashKey = config('capstone.encryption.blind-index-key');
        $this->remote_token = $socialiteUser->token;
        $this->remote_id_hash = hash_hmac('sha256', $socialiteUser->getId(), $hashKey);
        $this->remote_email_hash = hash_hmac('sha256', $socialiteUser->getEmail(), $hashKey);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function resourceable()
    {
        return $this->morphTo();
    }
}
