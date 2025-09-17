<?php

namespace Jiny\Auth\App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AdminBlacklistController extends Controller
{
    /**
     * 블랙리스트 목록
     * GET /admin/auth/blacklist
     */
    public function index(Request $request)
    {
        $query = DB::table('blacklists')
            ->where('is_whitelist', false);
        
        // 검색 필터
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('value', 'like', "%{$search}%")
                  ->orWhere('reason', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        // 타입 필터
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }
        
        // 상태 필터
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }
        
        // 만료 필터
        if ($request->has('expired')) {
            if ($request->expired === 'yes') {
                $query->whereNotNull('expires_at')
                      ->where('expires_at', '<', now());
            } elseif ($request->expired === 'no') {
                $query->where(function($q) {
                    $q->whereNull('expires_at')
                      ->orWhere('expires_at', '>=', now());
                });
            }
        }
        
        $blacklists = $query->orderBy('created_at', 'desc')->paginate(20);
        
        // 통계 정보
        $statistics = [
            'total' => DB::table('blacklists')->where('is_whitelist', false)->count(),
            'active' => DB::table('blacklists')->where('is_whitelist', false)->where('is_active', true)->count(),
            'email' => DB::table('blacklists')->where('is_whitelist', false)->where('type', 'email')->count(),
            'ip' => DB::table('blacklists')->where('is_whitelist', false)->where('type', 'ip')->count(),
            'domain' => DB::table('blacklists')->where('is_whitelist', false)->where('type', 'domain')->count(),
            'recent_blocks' => DB::table('blacklist_logs')
                ->where('action', 'blocked')
                ->where('created_at', '>=', now()->subDay())
                ->count(),
        ];
        
        return view('jiny-auth::admin.blacklist.index', compact('blacklists', 'statistics'));
    }
    
    /**
     * 이메일 블랙리스트 목록
     * GET /admin/auth/blacklist/email
     */
    public function emailList(Request $request)
    {
        $blacklists = DB::table('blacklists')
            ->where('type', 'email')
            ->where('is_whitelist', false)
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('jiny-auth::admin.blacklist.email', compact('blacklists'));
    }
    
    /**
     * IP 블랙리스트 목록
     * GET /admin/auth/blacklist/ip
     */
    public function ipList(Request $request)
    {
        $blacklists = DB::table('blacklists')
            ->where('type', 'ip')
            ->where('is_whitelist', false)
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        // IP 범위 체크 및 CIDR 표기 지원
        foreach ($blacklists as $blacklist) {
            if (strpos($blacklist->value, '/') !== false) {
                // CIDR 표기법
                list($ip, $cidr) = explode('/', $blacklist->value);
                $blacklist->is_range = true;
                $blacklist->ip_count = pow(2, (32 - $cidr));
            } elseif (strpos($blacklist->value, '-') !== false) {
                // IP 범위
                list($start, $end) = explode('-', $blacklist->value);
                $blacklist->is_range = true;
                $blacklist->ip_count = ip2long($end) - ip2long($start) + 1;
            } else {
                $blacklist->is_range = false;
                $blacklist->ip_count = 1;
            }
        }
        
        return view('jiny-auth::admin.blacklist.ip', compact('blacklists'));
    }
    
    /**
     * 이메일 블랙리스트 등록
     * POST /admin/auth/blacklist/email
     */
    public function addEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'value' => 'required|email',
            'reason' => 'required|string|max:255',
            'description' => 'nullable|string',
            'expires_at' => 'nullable|date|after:now',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        // 중복 체크
        $exists = DB::table('blacklists')
            ->where('type', 'email')
            ->where('value', $request->value)
            ->where('is_whitelist', false)
            ->exists();
        
        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => '이미 등록된 이메일입니다.'
            ], 409);
        }
        
        // 블랙리스트 등록
        $id = DB::table('blacklists')->insertGetId([
            'type' => 'email',
            'value' => $request->value,
            'reason' => $request->reason,
            'description' => $request->description,
            'is_active' => true,
            'is_whitelist' => false,
            'expires_at' => $request->expires_at,
            'added_by' => Auth::id(),
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        // 로그 기록
        $this->logActivity($id, 'added', 'email', $request->value, $request);
        
        return response()->json([
            'success' => true,
            'message' => '이메일이 블랙리스트에 추가되었습니다.'
        ]);
    }
    
    /**
     * IP 블랙리스트 등록
     * POST /admin/auth/blacklist/ip
     */
    public function addIp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'value' => 'required|string',
            'reason' => 'required|string|max:255',
            'description' => 'nullable|string',
            'expires_at' => 'nullable|date|after:now',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        // IP 유효성 검증
        if (!$this->validateIpFormat($request->value)) {
            return response()->json([
                'success' => false,
                'message' => '유효하지 않은 IP 형식입니다. 단일 IP, IP 범위(192.168.1.1-192.168.1.255), 또는 CIDR(192.168.1.0/24) 형식을 사용하세요.'
            ], 422);
        }
        
        // 중복 체크
        $exists = DB::table('blacklists')
            ->where('type', 'ip')
            ->where('value', $request->value)
            ->where('is_whitelist', false)
            ->exists();
        
        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => '이미 등록된 IP입니다.'
            ], 409);
        }
        
        // 블랙리스트 등록
        $id = DB::table('blacklists')->insertGetId([
            'type' => 'ip',
            'value' => $request->value,
            'reason' => $request->reason,
            'description' => $request->description,
            'is_active' => true,
            'is_whitelist' => false,
            'expires_at' => $request->expires_at,
            'added_by' => Auth::id(),
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        // 로그 기록
        $this->logActivity($id, 'added', 'ip', $request->value, $request);
        
        return response()->json([
            'success' => true,
            'message' => 'IP가 블랙리스트에 추가되었습니다.'
        ]);
    }
    
    /**
     * 블랙리스트 수정
     * PUT /admin/auth/blacklist/{id}
     */
    public function update(Request $request, $id)
    {
        $blacklist = DB::table('blacklists')->find($id);
        
        if (!$blacklist) {
            return response()->json([
                'success' => false,
                'message' => '블랙리스트를 찾을 수 없습니다.'
            ], 404);
        }
        
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'required|boolean',
            'expires_at' => 'nullable|date|after:now',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        // 블랙리스트 업데이트
        DB::table('blacklists')
            ->where('id', $id)
            ->update([
                'reason' => $request->reason,
                'description' => $request->description,
                'is_active' => $request->is_active,
                'expires_at' => $request->expires_at,
                'updated_at' => now()
            ]);
        
        // 로그 기록
        $this->logActivity($id, 'updated', $blacklist->type, $blacklist->value, $request);
        
        return response()->json([
            'success' => true,
            'message' => '블랙리스트가 수정되었습니다.'
        ]);
    }
    
    /**
     * 블랙리스트 해제
     * DELETE /admin/auth/blacklist/{id}
     */
    public function destroy(Request $request, $id)
    {
        $blacklist = DB::table('blacklists')->find($id);
        
        if (!$blacklist) {
            return response()->json([
                'success' => false,
                'message' => '블랙리스트를 찾을 수 없습니다.'
            ], 404);
        }
        
        // 로그 기록 (삭제 전)
        $this->logActivity($id, 'removed', $blacklist->type, $blacklist->value, $request);
        
        // 블랙리스트 삭제
        DB::table('blacklists')->where('id', $id)->delete();
        
        return response()->json([
            'success' => true,
            'message' => '블랙리스트에서 제거되었습니다.'
        ]);
    }
    
    /**
     * 일괄 블랙리스트 등록
     * POST /admin/auth/blacklist/bulk-add
     */
    public function bulkAdd(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:email,ip,domain,phone,keyword',
            'values' => 'required|string',
            'reason' => 'required|string|max:255',
            'description' => 'nullable|string',
            'expires_at' => 'nullable|date|after:now',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        // 값 파싱 (줄바꿈 또는 쉼표로 구분)
        $values = preg_split('/[\r\n,]+/', $request->values);
        $values = array_map('trim', $values);
        $values = array_filter($values); // 빈 값 제거
        $values = array_unique($values); // 중복 제거
        
        if (empty($values)) {
            return response()->json([
                'success' => false,
                'message' => '추가할 값이 없습니다.'
            ], 422);
        }
        
        $added = 0;
        $skipped = 0;
        $errors = [];
        
        foreach ($values as $value) {
            // 유효성 검증
            if ($request->type === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "{$value}: 유효하지 않은 이메일";
                $skipped++;
                continue;
            }
            
            if ($request->type === 'ip' && !$this->validateIpFormat($value)) {
                $errors[] = "{$value}: 유효하지 않은 IP";
                $skipped++;
                continue;
            }
            
            // 중복 체크
            $exists = DB::table('blacklists')
                ->where('type', $request->type)
                ->where('value', $value)
                ->where('is_whitelist', false)
                ->exists();
            
            if ($exists) {
                $skipped++;
                continue;
            }
            
            // 블랙리스트 추가
            $id = DB::table('blacklists')->insertGetId([
                'type' => $request->type,
                'value' => $value,
                'reason' => $request->reason,
                'description' => $request->description,
                'is_active' => true,
                'is_whitelist' => false,
                'expires_at' => $request->expires_at,
                'added_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            // 로그 기록
            $this->logActivity($id, 'bulk_added', $request->type, $value, $request);
            
            $added++;
        }
        
        return response()->json([
            'success' => true,
            'message' => "{$added}개 항목이 추가되었습니다. ({$skipped}개 건너뜀)",
            'added' => $added,
            'skipped' => $skipped,
            'errors' => $errors
        ]);
    }
    
    /**
     * 일괄 블랙리스트 해제
     * POST /admin/auth/blacklist/bulk-remove
     */
    public function bulkRemove(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:blacklists,id'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        $blacklists = DB::table('blacklists')
            ->whereIn('id', $request->ids)
            ->get();
        
        // 로그 기록
        foreach ($blacklists as $blacklist) {
            $this->logActivity($blacklist->id, 'bulk_removed', $blacklist->type, $blacklist->value, $request);
        }
        
        // 블랙리스트 삭제
        $deleted = DB::table('blacklists')
            ->whereIn('id', $request->ids)
            ->delete();
        
        return response()->json([
            'success' => true,
            'message' => "{$deleted}개 항목이 제거되었습니다."
        ]);
    }
    
    /**
     * 화이트리스트 관리
     * GET /admin/auth/blacklist/whitelist
     */
    public function whitelist(Request $request)
    {
        $query = DB::table('blacklists')
            ->where('is_whitelist', true);
        
        // 검색 필터
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('value', 'like', "%{$search}%")
                  ->orWhere('reason', 'like', "%{$search}%");
            });
        }
        
        // 타입 필터
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }
        
        $whitelists = $query->orderBy('created_at', 'desc')->paginate(20);
        
        return view('jiny-auth::admin.blacklist.whitelist', compact('whitelists'));
    }
    
    /**
     * 화이트리스트 등록
     * POST /admin/auth/blacklist/whitelist
     */
    public function addWhitelist(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:email,ip,domain',
            'value' => 'required|string',
            'reason' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        // 유효성 검증
        if ($request->type === 'email' && !filter_var($request->value, FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                'success' => false,
                'message' => '유효하지 않은 이메일 형식입니다.'
            ], 422);
        }
        
        if ($request->type === 'ip' && !$this->validateIpFormat($request->value)) {
            return response()->json([
                'success' => false,
                'message' => '유효하지 않은 IP 형식입니다.'
            ], 422);
        }
        
        // 중복 체크
        $exists = DB::table('blacklists')
            ->where('type', $request->type)
            ->where('value', $request->value)
            ->where('is_whitelist', true)
            ->exists();
        
        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => '이미 등록된 화이트리스트입니다.'
            ], 409);
        }
        
        // 화이트리스트 등록
        $id = DB::table('blacklists')->insertGetId([
            'type' => $request->type,
            'value' => $request->value,
            'reason' => $request->reason,
            'description' => $request->description,
            'is_active' => true,
            'is_whitelist' => true,
            'added_by' => Auth::id(),
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        // 로그 기록
        $this->logActivity($id, 'whitelist_added', $request->type, $request->value, $request);
        
        return response()->json([
            'success' => true,
            'message' => '화이트리스트에 추가되었습니다.'
        ]);
    }
    
    /**
     * IP 형식 검증
     */
    private function validateIpFormat($value)
    {
        // 단일 IP
        if (filter_var($value, FILTER_VALIDATE_IP)) {
            return true;
        }
        
        // CIDR 표기법 (예: 192.168.1.0/24)
        if (preg_match('/^(\d{1,3}\.){3}\d{1,3}\/\d{1,2}$/', $value)) {
            list($ip, $cidr) = explode('/', $value);
            return filter_var($ip, FILTER_VALIDATE_IP) && $cidr >= 0 && $cidr <= 32;
        }
        
        // IP 범위 (예: 192.168.1.1-192.168.1.255)
        if (preg_match('/^(\d{1,3}\.){3}\d{1,3}-(\d{1,3}\.){3}\d{1,3}$/', $value)) {
            list($start, $end) = explode('-', $value);
            return filter_var($start, FILTER_VALIDATE_IP) && filter_var($end, FILTER_VALIDATE_IP);
        }
        
        return false;
    }
    
    /**
     * 활동 로그 기록
     */
    private function logActivity($blacklistId, $action, $type, $value, $request)
    {
        DB::table('blacklist_logs')->insert([
            'blacklist_id' => $blacklistId,
            'action' => $action,
            'type' => $type,
            'value' => $value,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_id' => Auth::id(),
            'created_at' => now()
        ]);
    }
}