<?php

namespace Jiny\Auth\App\Services\Sharding;

use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Jiny\Auth\App\Models\UserShardingConfig;

class ShardingUserProvider implements UserProvider
{
    protected AuthShardingService $shardingService;

    public function __construct(AuthShardingService $shardingService)
    {
        $this->shardingService = $shardingService;
    }

    /**
     * Retrieve a user by their unique identifier.
     */
    public function retrieveById($identifier)
    {
        // 샤딩 설정 확인
        $shardingConfig = UserShardingConfig::getUserShardingConfig('users');

        if ($shardingConfig && $shardingConfig->isActive()) {
            // 샤딩이 활성화된 경우, 모든 샤드에서 검색
            return $this->findUserInShards($identifier, 'id');
        } else {
            // 일반 테이블에서 검색
            $user = DB::table('users')->where('id', $identifier)->first();
            return $user ? $this->createUserFromStdClass($user) : null;
        }
    }

    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     */
    public function retrieveByToken($identifier, $token)
    {
        // 샤딩 설정 확인
        $shardingConfig = UserShardingConfig::getUserShardingConfig('users');

        if ($shardingConfig && $shardingConfig->isActive()) {
            // 샤딩이 활성화된 경우, 모든 샤드에서 검색
            return $this->findUserInShards($identifier, 'id', 'remember_token', $token);
        } else {
            // 일반 테이블에서 검색
            $user = DB::table('users')
                ->where('id', $identifier)
                ->where('remember_token', $token)
                ->first();
            return $user ? $this->createUserFromStdClass($user) : null;
        }
    }

    /**
     * Update the "remember me" token for the given user in storage.
     */
    public function updateRememberToken(Authenticatable $user, $token)
    {
        $shardingConfig = UserShardingConfig::getUserShardingConfig('users');

        if ($shardingConfig && $shardingConfig->isActive()) {
            // 샤딩된 테이블에서 업데이트
            $shardTableName = $this->shardingService->getShardTableName('users', $user->getEmailForPasswordReset());
            if ($shardTableName && $shardTableName !== 'users') {
                DB::table($shardTableName)
                    ->where('id', $user->getAuthIdentifier())
                    ->update(['remember_token' => $token]);
            }
        } else {
            // 일반 테이블에서 업데이트
            DB::table('users')
                ->where('id', $user->getAuthIdentifier())
                ->update(['remember_token' => $token]);
        }
    }

    /**
     * Retrieve a user by the given credentials.
     */
    public function retrieveByCredentials(array $credentials)
    {
        if (empty($credentials) || !isset($credentials['email'])) {
            return null;
        }

        $email = $credentials['email'];

        // 샤딩 설정 확인
        $shardingConfig = UserShardingConfig::getUserShardingConfig('users');

        if ($shardingConfig && $shardingConfig->isActive()) {
            // 샤딩이 활성화된 경우, 이메일로 샤드 테이블 찾기
            $shardTableName = $this->shardingService->getShardTableName('users', $email);

            if ($shardTableName && $shardTableName !== 'users') {
                $user = DB::table($shardTableName)->where('email', $email)->first();
                return $user ? $this->createUserFromStdClass($user) : null;
            }
        } else {
            // 일반 테이블에서 검색
            $user = DB::table('users')->where('email', $email)->first();
            return $user ? $this->createUserFromStdClass($user) : null;
        }

        return null;
    }

    /**
     * Validate a user's credentials.
     */
    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        $plain = $credentials['password'];

        return Hash::check($plain, $user->getAuthPassword());
    }

    /**
     * 샤드 테이블들에서 사용자를 찾습니다.
     */
    protected function findUserInShards($value, $field, $additionalField = null, $additionalValue = null)
    {
        $shardingConfig = UserShardingConfig::getUserShardingConfig('users');

        if (!$shardingConfig) {
            return null;
        }

        $shardTableNames = $shardingConfig->getAllShardTableNames();

        foreach ($shardTableNames as $shardTableName) {
            $query = DB::table($shardTableName)->where($field, $value);

            if ($additionalField && $additionalValue) {
                $query->where($additionalField, $additionalValue);
            }

            $user = $query->first();

            if ($user) {
                return $this->createUserFromStdClass($user);
            }
        }

        return null;
    }

    /**
     * stdClass 객체로부터 Authenticatable 객체를 생성합니다.
     */
    protected function createUserFromStdClass($userData)
    {
        return new class($userData) implements Authenticatable {
            protected $userData;

            public function __construct($userData)
            {
                $this->userData = $userData;
            }

            public function getAuthIdentifierName()
            {
                return 'id';
            }

            public function getAuthIdentifier()
            {
                return $this->userData->id;
            }

            public function getAuthPassword()
            {
                return $this->userData->password;
            }

            public function getRememberToken()
            {
                return $this->userData->remember_token ?? null;
            }

            public function setRememberToken($value)
            {
                $this->userData->remember_token = $value;
            }

            public function getRememberTokenName()
            {
                return 'remember_token';
            }

            public function getEmailForPasswordReset()
            {
                return $this->userData->email;
            }

            // 사용자 데이터에 대한 매직 메서드
            public function __get($name)
            {
                return $this->userData->$name ?? null;
            }

            public function __isset($name)
            {
                return isset($this->userData->$name);
            }

            // 배열 접근 지원
            public function offsetGet($offset)
            {
                return $this->userData->$offset ?? null;
            }

            public function offsetExists($offset)
            {
                return isset($this->userData->$offset);
            }

            public function offsetSet($offset, $value)
            {
                $this->userData->$offset = $value;
            }

            public function offsetUnset($offset)
            {
                unset($this->userData->$offset);
            }
        };
    }
}
