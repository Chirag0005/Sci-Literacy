<?php

namespace App\Extensions;

use App\Models\User;
use App\Services\MongoService;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Facades\Hash;

class MongoUserProvider implements UserProvider
{
    /**
     * Retrieve a user by their unique identifier.
     *
     * @param  mixed  $identifier
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveById($identifier)
    {
        try {
            $userData = MongoService::execute('findOne', 'users', ['_id' => $identifier]);
            if (!$userData) {
                return null;
            }

            $user = new User();
            $user->forceFill($userData);
            $user->id = $userData['_id'];
            return $user;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     *
     * @param  mixed  $identifier
     * @param  string  $token
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByToken($identifier, $token)
    {
        return null;
    }

    /**
     * Update the "remember me" token for the given user in storage.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  string  $token
     * @return void
     */
    public function updateRememberToken(Authenticatable $user, $token)
    {
        // Not implemented
    }

    /**
     * Retrieve a user by the given credentials.
     *
     * @param  array  $credentials
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        if (empty($credentials) || (count($credentials) === 1 && array_key_exists('password', $credentials))) {
            return null;
        }

        $filter = [];
        foreach ($credentials as $key => $value) {
            if ($key !== 'password') {
                $filter[$key] = $value;
            }
        }

        try {
            $userData = MongoService::execute('findOne', 'users', $filter);
            if (!$userData) {
                return null;
            }

            $user = new User();
            $user->forceFill($userData);
            $user->id = $userData['_id'];
            return $user;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Validate a user against the given credentials.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  array  $credentials
     * @return bool
     */
    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        return Hash::check($credentials['password'], $user->getAuthPassword());
    }

    /**
     * Rehash the user's password if required.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  array  $credentials
     * @param  bool  $force
     * @return void
     */
    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false)
    {
        if (!$this->validateCredentials($user, $credentials)) {
            return;
        }

        if (Hash::needsRehash($user->getAuthPassword()) || $force) {
            $newPassword = Hash::make($credentials['password']);

            MongoService::execute('update', 'users', ['_id' => $user->getAuthIdentifier()], [
                'password' => $newPassword,
                'updated_at' => now()->toIso8601String()
            ]);

            $user->password = $newPassword;
        }
    }
}
