<?php

namespace App\Models;


use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\UploadedFile;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Zizaco\Entrust\Traits\EntrustUserTrait;

class User extends Authenticatable implements JWTSubject {

    use Notifiable, EntrustUserTrait;

    protected $table = 'rl_users';

    protected $fillable = [
        'name',
        'email',
        'role',
        'password',
        'link',
        'default_country',
        'avatar'
    ];

    protected $hidden = ['password', 'remember_token'];


    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function getAvatarAttribute($value)
    {
        return !empty($value) ? '/storage/'.$value : '';
    }
}
