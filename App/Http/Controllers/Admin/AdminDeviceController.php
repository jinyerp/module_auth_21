<?php

namespace Jiny\Auth\App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class AdminDeviceController extends Controller
{
    /**
     * 디바이스 목록
     * GET /admin/auth/devices
     */
    public function index(Request $request)
    {
        $query = DB::table('auth_user_devices')
            ->join('users', 'auth_user_devices.user_id', '=', 'users.id')
            ->select(
                'auth_user_devices.*',
                'users.name as user_name',
                'users.email as user_email'
            );
        
        // 검색
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('users.name', 'like', "%{$search}%")
                  ->orWhere('users.email', 'like', "%{$search}%")
                  ->orWhere('auth_user_devices.device_name', 'like', "%{$search}%")
                  ->orWhere('auth_user_devices.device_id', 'like', "%{$search}%")
                  ->orWhere('auth_user_devices.ip_address', 'like', "%{$search}%");
            });
        }
        
        // 필터
        if ($request->has('device_type')) {
            $query->where('auth_user_devices.device_type', $request->get('device_type'));
        }
        
        if ($request->has('platform')) {
            $query->where('auth_user_devices.platform', $request->get('platform'));
        }
        
        if ($request->has('is_blocked')) {
            $query->where('auth_user_devices.is_blocked', $request->get('is_blocked') === 'true');
        }
        
        if ($request->has('is_trusted')) {
            $query->where('auth_user_devices.is_trusted', $request->get('is_trusted') === 'true');
        }
        
        $devices = $query->orderBy('auth_user_devices.last_active_at', 'desc')
            ->paginate(20);
        
        // 통계
        $stats = [
            'total_devices' => DB::table('auth_user_devices')->count(),
            'active_devices' => DB::table('auth_user_devices')
                ->where('last_active_at', '>=', now()->subDays(30))
                ->count(),
            'blocked_devices' => DB::table('auth_user_devices')->where('is_blocked', true)->count(),
            'trusted_devices' => DB::table('auth_user_devices')->where('is_trusted', true)->count(),
        ];
        
        // 디바이스 유형 분포
        $deviceTypes = DB::table('auth_user_devices')
            ->select('device_type', DB::raw('COUNT(*) as count'))
            ->groupBy('device_type')
            ->get();
        
        // 플랫폼 분포
        $platforms = DB::table('auth_user_devices')
            ->select('platform', DB::raw('COUNT(*) as count'))
            ->whereNotNull('platform')
            ->groupBy('platform')
            ->get();
        
        return view('jiny-auth::admin.devices.index', compact('devices', 'stats', 'deviceTypes', 'platforms'));
    }
    
    /**
     * 디바이스 상세
     * GET /admin/auth/devices/{id}
     */
    public function show(Request $request, $id)
    {
        $device = DB::table('auth_user_devices')
            ->join('users', 'auth_user_devices.user_id', '=', 'users.id')
            ->leftJoin('users as blocked_admin', 'auth_user_devices.blocked_by', '=', 'blocked_admin.id')
            ->select(
                'auth_user_devices.*',
                'users.name as user_name',
                'users.email as user_email',
                'blocked_admin.name as blocked_by_name'
            )
            ->where('auth_user_devices.id', $id)
            ->first();
        
        if (!$device) {
            return redirect()->route('admin.auth.devices')
                ->with('error', '디바이스를 찾을 수 없습니다.');
        }
        
        $device->capabilities = json_decode($device->capabilities, true) ?? [];
        
        // 최근 로그인 기록
        $loginLogs = DB::table('auth_device_login_logs')
            ->where('device_id', $id)
            ->orderBy('logged_at', 'desc')
            ->limit(50)
            ->get();
        
        // 로그인 통계
        $loginStats = [
            'total_logins' => DB::table('auth_device_login_logs')->where('device_id', $id)->count(),
            'success_logins' => DB::table('auth_device_login_logs')->where('device_id', $id)->where('status', 'success')->count(),
            'failed_logins' => DB::table('auth_device_login_logs')->where('device_id', $id)->where('status', 'failed')->count(),
            'blocked_logins' => DB::table('auth_device_login_logs')->where('device_id', $id)->where('status', 'blocked')->count(),
        ];
        
        // 동일 사용자의 다른 디바이스
        $otherDevices = DB::table('auth_user_devices')
            ->where('user_id', $device->user_id)
            ->where('id', '!=', $id)
            ->orderBy('last_active_at', 'desc')
            ->get();
        
        return view('jiny-auth::admin.devices.show', compact('device', 'loginLogs', 'loginStats', 'otherDevices'));
    }
    
    /**
     * 디바이스 차단
     * POST /admin/auth/devices/{id}/block
     */
    public function block(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:255',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        $device = DB::table('auth_user_devices')->where('id', $id)->first();
        
        if (!$device) {
            return response()->json([
                'success' => false,
                'message' => '디바이스를 찾을 수 없습니다.'
            ], 404);
        }
        
        if ($device->is_blocked) {
            return response()->json([
                'success' => false,
                'message' => '이미 차단된 디바이스입니다.'
            ], 400);
        }
        
        DB::table('auth_user_devices')->where('id', $id)->update([
            'is_blocked' => true,
            'blocked_reason' => $request->reason,
            'blocked_at' => now(),
            'blocked_by' => auth()->id(),
            'updated_at' => now(),
        ]);
        
        // 차단 로그 기록
        DB::table('auth_device_login_logs')->insert([
            'device_id' => $id,
            'user_id' => $device->user_id,
            'ip_address' => $device->ip_address,
            'status' => 'blocked',
            'failure_reason' => 'Device blocked by admin: ' . $request->reason,
            'logged_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        return response()->json([
            'success' => true,
            'message' => '디바이스가 차단되었습니다.'
        ]);
    }
    
    /**
     * 디바이스 차단 해제
     * POST /admin/auth/devices/{id}/unblock
     */
    public function unblock(Request $request, $id)
    {
        $device = DB::table('auth_user_devices')->where('id', $id)->first();
        
        if (!$device) {
            return response()->json([
                'success' => false,
                'message' => '디바이스를 찾을 수 없습니다.'
            ], 404);
        }
        
        if (!$device->is_blocked) {
            return response()->json([
                'success' => false,
                'message' => '차단되지 않은 디바이스입니다.'
            ], 400);
        }
        
        DB::table('auth_user_devices')->where('id', $id)->update([
            'is_blocked' => false,
            'blocked_reason' => null,
            'blocked_at' => null,
            'blocked_by' => null,
            'updated_at' => now(),
        ]);
        
        return response()->json([
            'success' => true,
            'message' => '디바이스 차단이 해제되었습니다.'
        ]);
    }
    
    /**
     * 디바이스 신뢰 설정
     * POST /admin/auth/devices/{id}/trust
     */
    public function trust(Request $request, $id)
    {
        $device = DB::table('auth_user_devices')->where('id', $id)->first();
        
        if (!$device) {
            return response()->json([
                'success' => false,
                'message' => '디바이스를 찾을 수 없습니다.'
            ], 404);
        }
        
        DB::table('auth_user_devices')->where('id', $id)->update([
            'is_trusted' => true,
            'updated_at' => now(),
        ]);
        
        return response()->json([
            'success' => true,
            'message' => '디바이스가 신뢰 목록에 추가되었습니다.'
        ]);
    }
    
    /**
     * 디바이스 신뢰 해제
     * POST /admin/auth/devices/{id}/untrust
     */
    public function untrust(Request $request, $id)
    {
        $device = DB::table('auth_user_devices')->where('id', $id)->first();
        
        if (!$device) {
            return response()->json([
                'success' => false,
                'message' => '디바이스를 찾을 수 없습니다.'
            ], 404);
        }
        
        DB::table('auth_user_devices')->where('id', $id)->update([
            'is_trusted' => false,
            'updated_at' => now(),
        ]);
        
        return response()->json([
            'success' => true,
            'message' => '디바이스가 신뢰 목록에서 제거되었습니다.'
        ]);
    }
    
    /**
     * 디바이스 삭제
     * DELETE /admin/auth/devices/{id}
     */
    public function destroy(Request $request, $id)
    {
        $device = DB::table('auth_user_devices')->where('id', $id)->first();
        
        if (!$device) {
            return response()->json([
                'success' => false,
                'message' => '디바이스를 찾을 수 없습니다.'
            ], 404);
        }
        
        DB::table('auth_user_devices')->where('id', $id)->delete();
        
        return response()->json([
            'success' => true,
            'message' => '디바이스가 삭제되었습니다.'
        ]);
    }
    
    /**
     * 디바이스 통계
     * GET /admin/auth/devices/statistics
     */
    public function statistics(Request $request)
    {
        // 일별 활성 디바이스
        $dailyActive = DB::table('auth_user_devices')
            ->select(
                DB::raw('DATE(last_active_at) as date'),
                DB::raw('COUNT(DISTINCT id) as device_count'),
                DB::raw('COUNT(DISTINCT user_id) as user_count')
            )
            ->where('last_active_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();
        
        // 디바이스 타입별 분포
        $deviceTypeDistribution = DB::table('auth_user_devices')
            ->select(
                'device_type',
                DB::raw('COUNT(*) as count'),
                DB::raw('COUNT(DISTINCT user_id) as user_count')
            )
            ->groupBy('device_type')
            ->get();
        
        // 플랫폼별 분포
        $platformDistribution = DB::table('auth_user_devices')
            ->select(
                'platform',
                DB::raw('COUNT(*) as count'),
                DB::raw('COUNT(DISTINCT user_id) as user_count')
            )
            ->whereNotNull('platform')
            ->groupBy('platform')
            ->get();
        
        // 브라우저별 분포
        $browserDistribution = DB::table('auth_user_devices')
            ->select(
                'browser',
                DB::raw('COUNT(*) as count')
            )
            ->whereNotNull('browser')
            ->groupBy('browser')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();
        
        // 사용자당 디바이스 수 분포
        $devicesPerUser = DB::table('auth_user_devices')
            ->select(
                DB::raw('COUNT(*) as device_count'),
                DB::raw('COUNT(DISTINCT user_id) as user_count')
            )
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) > 0')
            ->get()
            ->groupBy('device_count')
            ->map(function ($group) {
                return count($group);
            });
        
        // 최근 차단된 디바이스
        $recentBlocked = DB::table('auth_user_devices')
            ->join('users', 'auth_user_devices.user_id', '=', 'users.id')
            ->leftJoin('users as blocked_admin', 'auth_user_devices.blocked_by', '=', 'blocked_admin.id')
            ->select(
                'auth_user_devices.*',
                'users.name as user_name',
                'users.email as user_email',
                'blocked_admin.name as blocked_by_name'
            )
            ->where('auth_user_devices.is_blocked', true)
            ->orderBy('auth_user_devices.blocked_at', 'desc')
            ->limit(20)
            ->get();
        
        return view('jiny-auth::admin.devices.statistics', compact(
            'dailyActive',
            'deviceTypeDistribution',
            'platformDistribution',
            'browserDistribution',
            'devicesPerUser',
            'recentBlocked'
        ));
    }
    
    /**
     * 사용자별 디바이스 목록
     * GET /admin/auth/users/{userId}/devices
     */
    public function userDevices(Request $request, $userId)
    {
        $user = User::find($userId);
        
        if (!$user) {
            return redirect()->route('admin.auth.devices')
                ->with('error', '사용자를 찾을 수 없습니다.');
        }
        
        $devices = DB::table('auth_user_devices')
            ->where('user_id', $userId)
            ->orderBy('last_active_at', 'desc')
            ->get();
        
        foreach ($devices as $device) {
            $device->capabilities = json_decode($device->capabilities, true) ?? [];
        }
        
        return view('jiny-auth::admin.devices.user', compact('user', 'devices'));
    }
}