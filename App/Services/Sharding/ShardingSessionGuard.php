<?php

namespace Jiny\Auth\App\Services\Sharding;

use Illuminate\Auth\SessionGuard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ShardingSessionGuard extends SessionGuard
{
    protected AuthShardingService $shardingService;

    public function __construct(
        $name,
        UserProvider $provider,
        AuthShardingService $shardingService,
        Request $request = null
    ) {
        parent::__construct($name, $provider, $request);
        $this->shardingService = $shardingService;
    }

    /**
     * 사용자 인증을 시도합니다.
     */
    public function attempt(array $credentials = [], $remember = false)
    {
        $this->fireAttemptEvent($credentials, $remember);

        $this->lastAttempted = $user = $this->provider->retrieveByCredentials($credentials);

        // 사용자가 존재하고 비밀번호가 일치하는 경우
        if ($this->hasValidCredentials($user, $credentials)) {
            $this->login($user, $remember);

            return true;
        }

        // 인증 실패 시 이벤트 발생
        $this->fireFailedEvent($user, $credentials);

        return false;
    }

    /**
     * 유효한 인증 정보인지 확인합니다.
     */
    protected function hasValidCredentials($user, $credentials)
    {
        return $user !== null && $this->provider->validateCredentials($user, $credentials);
    }

    /**
     * 사용자를 로그인시킵니다.
     */
    public function login($user, $remember = false)
    {
        $this->updateSession($user->getAuthIdentifier());

        // Remember me 토큰 설정
        if ($remember) {
            $this->ensureRememberTokenIsSet($user);
            $this->queueRecallerCookie($user);
        }

        // 로그인 이벤트 발생
        $this->fireLoginEvent($user, $remember);

        $this->setUser($user);

        // 마지막 로그인 시간 업데이트
        $this->updateLastLoginTime($user);
    }

    /**
     * 마지막 로그인 시간을 업데이트합니다.
     */
    protected function updateLastLoginTime($user)
    {
        try {
            $email = $user->getEmailForPasswordReset();
            $userId = $user->getAuthIdentifier();

            // 샤딩 설정 확인
            $shardingConfig = \Jiny\Auth\App\Models\UserShardingConfig::getUserShardingConfig('users');

            if ($shardingConfig && $shardingConfig->isActive()) {
                // 샤딩된 테이블에서 업데이트
                $shardTableName = $this->shardingService->getShardTableName('users', $email);
                if ($shardTableName && $shardTableName !== 'users') {
                    \Illuminate\Support\Facades\DB::table($shardTableName)
                        ->where('id', $userId)
                        ->update([
                            'last_login_at' => now(),
                            'updated_at' => now()
                        ]);
                }
            } else {
                // 일반 테이블에서 업데이트
                \Illuminate\Support\Facades\DB::table('users')
                    ->where('id', $userId)
                    ->update([
                        'last_login_at' => now(),
                        'updated_at' => now()
                    ]);
            }
        } catch (\Exception $e) {
            Log::error('마지막 로그인 시간 업데이트 실패: ' . $e->getMessage());
        }
    }

    /**
     * 사용자 비밀번호를 업데이트합니다.
     */
    public function updatePassword($user, $newPassword)
    {
        $email = $user->getEmailForPasswordReset();
        $userId = $user->getAuthIdentifier();
        $hashedPassword = Hash::make($newPassword);

        // 샤딩 설정 확인
        $shardingConfig = \Jiny\Auth\Models\UserShardingConfig::getUserShardingConfig('users');

        if ($shardingConfig && $shardingConfig->isActive()) {
            // 샤딩된 테이블에서 업데이트
            $shardTableName = $this->shardingService->getShardTableName('users', $email);
            if ($shardTableName && $shardTableName !== 'users') {
                return \Illuminate\Support\Facades\DB::table($shardTableName)
                    ->where('id', $userId)
                    ->update([
                        'password' => $hashedPassword,
                        'updated_at' => now()
                    ]);
            }
        } else {
            // 일반 테이블에서 업데이트
            return \Illuminate\Support\Facades\DB::table('users')
                ->where('id', $userId)
                ->update([
                    'password' => $hashedPassword,
                    'updated_at' => now()
                ]);
        }

        return false;
    }

    /**
     * 사용자 정보를 업데이트합니다.
     */
    public function updateUser($user, array $data)
    {
        $email = $user->getEmailForPasswordReset();
        $userId = $user->getAuthIdentifier();

        // 샤딩 설정 확인
        $shardingConfig = \Jiny\Auth\Models\UserShardingConfig::getUserShardingConfig('users');

        if ($shardingConfig && $shardingConfig->isActive()) {
            // 샤딩된 테이블에서 업데이트
            $shardTableName = $this->shardingService->getShardTableName('users', $email);
            if ($shardTableName && $shardTableName !== 'users') {
                return \Illuminate\Support\Facades\DB::table($shardTableName)
                    ->where('id', $userId)
                    ->update(array_merge($data, ['updated_at' => now()]));
            }
        } else {
            // 일반 테이블에서 업데이트
            return \Illuminate\Support\Facades\DB::table('users')
                ->where('id', $userId)
                ->update(array_merge($data, ['updated_at' => now()]));
        }

        return false;
    }
}
