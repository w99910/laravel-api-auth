<?php

namespace Zlt\LaravelApiAuth\Support;

use Jenssegers\Mongodb\Eloquent\Model;
use Laravel\Sanctum\Contracts\HasAbilities;

class MongoPersonalAccessToken extends Model implements HasAbilities
{
    protected $connection = 'mongodb';
    protected $collection = 'personal_access_tokens';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'abilities' => 'json',
        'last_used_at' => 'datetime',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'token',
        'abilities',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'token',
    ];

    public function tokenable(): \Illuminate\Database\Eloquent\Relations\MorphTo|\Jenssegers\Mongodb\Relations\MorphTo
    {
        return $this->morphTo('tokenable');
    }

    /**
     * Find the token instance matching the given token.
     *
     * @param string $token
     * @return Model|MongoPersonalAccessToken|null
     */
    public static function findToken(string $token): Model|MongoPersonalAccessToken|null
    {
        if (!str_contains($token, '|')) {
            return static::where('token', hash('sha256', $token))->first();
        }

        [$id, $token] = explode('|', $token, 2);

        if ($instance = static::find($id)) {
            return hash_equals($instance->token, hash('sha256', $token)) ? $instance : null;
        }

        return null;
    }

    /**
     * Determine if the token has a given ability.
     *
     * @param string $ability
     * @return bool
     */
    public function can($ability): bool
    {
        return in_array('*', $this->abilities) ||
            array_key_exists($ability, array_flip($this->abilities));
    }

    /**
     * Determine if the token is missing a given ability.
     *
     * @param string $ability
     * @return bool
     */
    public function cant($ability): bool
    {
        return !$this->can($ability);
    }
}
