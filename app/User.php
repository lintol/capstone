<?php

namespace App;

use Hash;
use App\RemoteUser;
use Socialite;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Alsofronie\Uuid\UuidModelTrait;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use UuidModelTrait, Notifiable, HasRoles, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'primary_remote_user_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token'
    ];

    /**
     * Retrieve details temporarily from OAuth provider
     *
     * @param bool $all      if true, retrieve all OAuth user attributes
     * @return bool $success
     */
    public function retrieve($all = false)
    {
        if ($this->primaryRemoteUser) {
            $this->primaryRemoteUser->retrieve();
        }
    }

    public function getEmailAttribute($email)
    {
        if ($email === null && $this->primaryRemoteUser !== null) {
            return $this->primaryRemoteUser->remoteEmail;
        }

        return $email;
    }

    public function getDriverServerAttribute()
    {
        if ($this->primaryRemoteUser !== null) {
            return $this->primaryRemoteUser->driver_server;
        }
        return null;
    }

    public function getPresentAttribute()
    {
        return $this->id !== null && strpos($this->id, 'remote') === false;
    }

    public function getDriverAttribute()
    {
        if ($this->primaryRemoteUser !== null) {
            return $this->primaryRemoteUser->driver;
        }
        return null;
    }

    public function getNameAttribute($name)
    {
        if ($name === null && $this->primaryRemoteUser !== null) {
            return $this->primaryRemoteUser->remoteName;
        }
        return $name;
    }

    public function getNicknameAttribute($nickname)
      {
        if ($nickname === null && $this->primaryRemoteUser !== null) {
            return $this->primaryRemoteUser->remoteNickname;
        }
        return $nickname;
    }

    public function getAvatarAttribute($avatar)
    {
        if ($avatar === null && $this->primaryRemoteUser !== null) {
            return $this->primaryRemoteUser->remoteAvatar;
        }
        return $avatar;
    }

    public static function findByRemote($driver, $driverServer, $socialiteUser, $create=false)
    {
        $hash = Hash::make($socialiteUser->getId());

        $remoteUser = RemoteUser::whereDriver($driver)
            ->whereDriverServer($driverServer)
            ->whereRemoteIdHash($hash)
            ->first();

        $user = null;

        if ($remoteUser) {
            $user = $remoteUser->user;
        } elseif ($create) {
            $user = User::create();

            $remoteUser = RemoteUser::create([
                'user_id' => $user->id,
                'driver' => $driver,
                'driver_server' => $driverServer,
                'remote_id_hash' => $hash
            ]);

            $user->primary_remote_user_id = $remoteUser->id;
            $user->save();
        }

        return $user;
    }

    public function saveRemote($socialiteUser)
    {
        if (!$this->primaryRemoteUser) {
            throw RuntimeException(__("Must connect remote user before saving their details"));
        }

        $this->primaryRemoteUser->updateFromSocialite($socialiteUser);
        $this->primaryRemoteUser->save();
    }

    public function primaryRemoteUser()
    {
        return $this->belongsTo(RemoteUser::class);
    }
}
