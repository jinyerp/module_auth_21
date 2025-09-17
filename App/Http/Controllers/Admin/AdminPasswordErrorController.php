<?php

namespace Jiny\Auth\App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Jiny\Auth\App\Models\UserPasswordError;
use Jiny\Auth\App\Models\User;

/**
 * AdminPasswordErrorController
 *
 * 비밀번호 오류 관리 컨트롤러
 * 비밀번호 오류로 인한 계정 잠금 및 해제를 관리
 *
 * @package Jiny\Auth\App\Http\Controllers\Admin
 * @author JinyPHP
 * @version 1.0.0
 * @since 1.0.0
 * @license MIT
 *
 * @docs docs/features/AdminPasswordError.md
 *
 * 🔄 기능 수정 시 테스트 실행 필요:
 * 이 컨트롤러의 기능이 수정되면 다음 테스트를 반드시 실행해주세요:
 *
 * ```bash
 * # 전체 비밀번호 오류 관리 테스트 실행
 * php artisan test jiny/auth/tests/Feature/AdminPasswordErrorControllerTest.php
 * ```
 *
 * 📋 주요 테스트 항목:
 * 1. 비밀번호 오류 목록 조회 및 필터링 테스트
 * 2. 비밀번호 오류 상세 정보 조회 테스트
 * 3. 계정 잠금 해제 처리 테스트
 * 4. 잠금된 계정 목록 조회 테스트
 * 5. 영구 잠금된 계정 목록 조회 테스트
 * 6. 비밀번호 오류 통계 조회 테스트
 * 7. 비밀번호 오류 기록 삭제 테스트
 * 8. 연속 오류 횟수 기반 잠금 처리 테스트
 * 9. IP 주소별 오류 추적 테스트
 * 10. 권한 검증 및 보안 테스트
 *
 * ⚠️ 주의사항:
 * - 계정 잠금 해제 시 consecutive_errors 초기화 확인
 * - 영구 잠금 기준값 설정 확인
 * - 잠금 해제 시 unlock_reason 기록 확인
 * - 통계 데이터 실시간 업데이트 확인
 * - IP 주소별 오류 패턴 분석 확인
 * - 잠금 상태 변경 시 사용자 상태 동기화 확인
 */
class AdminPasswordErrorController extends Controller
{
    // 뷰 경로 변수 정의
    public $indexPath = 'jiny-auth::admin.password-errors.index';
    public $showPath = 'jiny-auth::admin.password-errors.show';
    public $lockedPath = 'jiny-auth::admin.password-errors.locked';
    public $permanentlyLockedPath = 'jiny-auth::admin.password-errors.permanently-locked';
    public $statisticsPath = 'jiny-auth::admin.password-errors.statistics';

    /**
     * 비밀번호 오류 목록 표시
     */
    public function index(Request $request): View
    {
        $query = UserPasswordError::with('user')
            ->orderBy('created_at', 'desc');

        // 검색 필터 적용
        if ($request->filled('email')) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }

        if ($request->filled('ip_address')) {
            $query->where('ip_address', 'like', '%' . $request->ip_address . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        $passwordErrors = $query->paginate(20);

        // 통계 데이터
        $stats = [
            'total_errors' => UserPasswordError::count(),
            'locked_accounts' => UserPasswordError::where('status', 'locked')->count(),
            'permanently_locked' => UserPasswordError::where('consecutive_errors', '>=', config('admin.auth.login.permanent_lockout_attempts', 25))->count(),
            'today_errors' => UserPasswordError::whereDate('created_at', today())->count(),
        ];

        return view($this->indexPath, compact('passwordErrors', 'stats'));
    }

    /**
     * 비밀번호 오류 상세 정보 표시
     */
    public function show(UserPasswordError $passwordError): View
    {
        return view($this->showPath, compact('passwordError'));
    }

    /**
     * 계정 잠금 해제
     */
    public function unlock(Request $request, UserPasswordError $passwordError): RedirectResponse
    {
        $passwordError->update([
            'status' => 'active',
            'locked_at' => null,
            'consecutive_errors' => 0,
            'unlock_reason' => $request->input('unlock_reason', '관리자에 의한 잠금 해제'),
            'unlocked_at' => now(),
        ]);

        return redirect()->route('admin.auth.password-errors.index')
            ->with('success', '계정이 성공적으로 잠금 해제되었습니다.');
    }

    /**
     * 잠금된 계정 목록
     */
    public function locked(): View
    {
        $passwordErrors = UserPasswordError::with('user')
            ->where('status', 'locked')
            ->where('consecutive_errors', '>=', config('admin.auth.login.lockout_attempts', 5))
            ->where('consecutive_errors', '<', config('admin.auth.login.permanent_lockout_attempts', 25))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view($this->lockedPath, compact('passwordErrors'));
    }

    /**
     * 영구 잠금된 계정 목록
     */
    public function permanentlyLocked(): View
    {
        $passwordErrors = UserPasswordError::with('user')
            ->where('consecutive_errors', '>=', config('admin.auth.login.permanent_lockout_attempts', 25))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view($this->permanentlyLockedPath, compact('passwordErrors'));
    }

    /**
     * 통계 정보
     */
    public function statistics(): View
    {
        $stats = [
            'total_errors' => UserPasswordError::count(),
            'unique_emails' => UserPasswordError::distinct('email')->count(),
            'unique_ips' => UserPasswordError::distinct('ip_address')->count(),
            'locked_accounts' => UserPasswordError::where('status', 'locked')->count(),
            'permanently_locked' => UserPasswordError::where('consecutive_errors', '>=', config('admin.auth.login.permanent_lockout_attempts', 25))->count(),
            'today_errors' => UserPasswordError::whereDate('created_at', today())->count(),
            'this_week_errors' => UserPasswordError::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month_errors' => UserPasswordError::whereMonth('created_at', now()->month)->count(),
        ];

        // 오류 타입별 통계
        $errorTypes = UserPasswordError::selectRaw('error_type, COUNT(*) as count')
            ->groupBy('error_type')
            ->orderBy('count', 'desc')
            ->get();

        // 일별 오류 통계 (최근 30일)
        $dailyErrors = UserPasswordError::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->whereBetween('created_at', [now()->subDays(30), now()])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view($this->statisticsPath, compact('stats', 'errorTypes', 'dailyErrors'));
    }

    /**
     * 비밀번호 오류 기록 삭제
     */
    public function destroy(UserPasswordError $passwordError): RedirectResponse
    {
        $passwordError->delete();

        return redirect()->route('admin.auth.password-errors.index')
            ->with('success', '비밀번호 오류 기록이 삭제되었습니다.');
    }
}
