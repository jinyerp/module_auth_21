<?php

namespace Jiny\Auth\App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class AdminPointController extends Controller
{
    /**
     * 포인트 목록
     * GET /admin/auth/points
     */
    public function index(Request $request)
    {
        $query = DB::table('auth_user_points')
            ->join('users', 'auth_user_points.user_id', '=', 'users.id')
            ->select(
                'auth_user_points.*',
                'users.name as user_name',
                'users.email as user_email'
            );
        
        // 검색
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('users.name', 'like', "%{$search}%")
                  ->orWhere('users.email', 'like', "%{$search}%");
            });
        }
        
        // 필터
        if ($request->has('min_balance')) {
            $query->where('auth_user_points.balance', '>=', $request->get('min_balance'));
        }
        
        if ($request->has('max_balance')) {
            $query->where('auth_user_points.balance', '<=', $request->get('max_balance'));
        }
        
        $points = $query->orderBy('auth_user_points.balance', 'desc')
            ->paginate(20);
        
        // 통계
        $stats = [
            'total_users' => DB::table('auth_user_points')->count(),
            'total_balance' => DB::table('auth_user_points')->sum('balance'),
            'total_earned' => DB::table('auth_user_points')->sum('total_earned'),
            'total_used' => DB::table('auth_user_points')->sum('total_used'),
            'total_expired' => DB::table('auth_user_points')->sum('total_expired'),
            'avg_balance' => DB::table('auth_user_points')->avg('balance'),
        ];
        
        return view('jiny-auth::admin.points.index', compact('points', 'stats'));
    }
    
    /**
     * 포인트 적립
     * POST /admin/auth/points/{userId}/add
     */
    public function add(Request $request, $userId)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1',
            'action' => 'required|string|max:50',
            'description' => 'required|string|max:255',
            'expire_date' => 'nullable|date|after:today',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        $user = User::find($userId);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => '사용자를 찾을 수 없습니다.'
            ], 404);
        }
        
        DB::beginTransaction();
        
        try {
            // 포인트 지갑 확인 또는 생성
            $pointWallet = DB::table('auth_user_points')
                ->where('user_id', $userId)
                ->first();
            
            if (!$pointWallet) {
                DB::table('auth_user_points')->insert([
                    'user_id' => $userId,
                    'balance' => 0,
                    'total_earned' => 0,
                    'total_used' => 0,
                    'total_expired' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                $pointWallet = DB::table('auth_user_points')
                    ->where('user_id', $userId)
                    ->first();
            }
            
            $newBalance = $pointWallet->balance + $request->amount;
            
            // 포인트 거래 기록
            DB::table('auth_point_transactions')->insert([
                'user_id' => $userId,
                'type' => 'earn',
                'action' => $request->action,
                'amount' => $request->amount,
                'balance_before' => $pointWallet->balance,
                'balance_after' => $newBalance,
                'description' => $request->description,
                'expire_date' => $request->expire_date,
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // 포인트 잔액 업데이트
            DB::table('auth_user_points')
                ->where('user_id', $userId)
                ->update([
                    'balance' => $newBalance,
                    'total_earned' => DB::raw('total_earned + ' . $request->amount),
                    'last_earned_at' => now(),
                    'updated_at' => now(),
                ]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => '포인트가 적립되었습니다.',
                'new_balance' => $newBalance
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => '포인트 적립에 실패했습니다.'
            ], 500);
        }
    }
    
    /**
     * 포인트 차감
     * POST /admin/auth/points/{userId}/deduct
     */
    public function deduct(Request $request, $userId)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1',
            'action' => 'required|string|max:50',
            'description' => 'required|string|max:255',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        $user = User::find($userId);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => '사용자를 찾을 수 없습니다.'
            ], 404);
        }
        
        DB::beginTransaction();
        
        try {
            // 포인트 지갑 확인
            $pointWallet = DB::table('auth_user_points')
                ->where('user_id', $userId)
                ->first();
            
            if (!$pointWallet || $pointWallet->balance < $request->amount) {
                return response()->json([
                    'success' => false,
                    'message' => '포인트가 부족합니다.'
                ], 400);
            }
            
            $newBalance = $pointWallet->balance - $request->amount;
            
            // 포인트 거래 기록
            DB::table('auth_point_transactions')->insert([
                'user_id' => $userId,
                'type' => 'use',
                'action' => $request->action,
                'amount' => -$request->amount,
                'balance_before' => $pointWallet->balance,
                'balance_after' => $newBalance,
                'description' => $request->description,
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // 포인트 잔액 업데이트
            DB::table('auth_user_points')
                ->where('user_id', $userId)
                ->update([
                    'balance' => $newBalance,
                    'total_used' => DB::raw('total_used + ' . $request->amount),
                    'last_used_at' => now(),
                    'updated_at' => now(),
                ]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => '포인트가 차감되었습니다.',
                'new_balance' => $newBalance
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => '포인트 차감에 실패했습니다.'
            ], 500);
        }
    }
    
    /**
     * 포인트 내역
     * GET /admin/auth/points/{userId}/history
     */
    public function history(Request $request, $userId)
    {
        $user = User::find($userId);
        if (!$user) {
            return redirect()->route('admin.auth.points')
                ->with('error', '사용자를 찾을 수 없습니다.');
        }
        
        // 포인트 지갑
        $pointWallet = DB::table('auth_user_points')
            ->where('user_id', $userId)
            ->first();
        
        if (!$pointWallet) {
            $pointWallet = (object)[
                'balance' => 0,
                'total_earned' => 0,
                'total_used' => 0,
                'total_expired' => 0,
            ];
        }
        
        // 거래 내역
        $query = DB::table('auth_point_transactions')
            ->where('user_id', $userId);
        
        // 필터
        if ($request->has('type')) {
            $query->where('type', $request->get('type'));
        }
        
        if ($request->has('action')) {
            $query->where('action', $request->get('action'));
        }
        
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->get('date_from'));
        }
        
        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->get('date_to'));
        }
        
        $transactions = $query->orderBy('created_at', 'desc')
            ->paginate(20);
        
        // 통계
        $stats = [
            'this_month_earned' => DB::table('auth_point_transactions')
                ->where('user_id', $userId)
                ->where('type', 'earn')
                ->whereMonth('created_at', now()->month)
                ->sum('amount'),
            'this_month_used' => DB::table('auth_point_transactions')
                ->where('user_id', $userId)
                ->where('type', 'use')
                ->sum(DB::raw('ABS(amount)')),
            'expiring_soon' => DB::table('auth_point_transactions')
                ->where('user_id', $userId)
                ->where('type', 'earn')
                ->where('is_expired', false)
                ->whereBetween('expire_date', [now(), now()->addDays(30)])
                ->sum('amount'),
        ];
        
        return view('jiny-auth::admin.points.history', compact('user', 'pointWallet', 'transactions', 'stats'));
    }
    
    /**
     * 포인트 통계
     * GET /admin/auth/points/statistics
     */
    public function statistics(Request $request)
    {
        // 일별 포인트 발급/사용 추이
        $dailyStats = DB::table('auth_point_transactions')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(CASE WHEN type = "earn" THEN amount ELSE 0 END) as earned'),
                DB::raw('SUM(CASE WHEN type = "use" THEN ABS(amount) ELSE 0 END) as used'),
                DB::raw('COUNT(DISTINCT user_id) as unique_users')
            )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();
        
        // 액션별 포인트 발급 통계
        $actionStats = DB::table('auth_point_transactions')
            ->select(
                'action',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('AVG(amount) as avg_amount')
            )
            ->where('type', 'earn')
            ->groupBy('action')
            ->orderBy('total_amount', 'desc')
            ->get();
        
        // 포인트 보유 분포
        $balanceDistribution = DB::table('auth_user_points')
            ->select(
                DB::raw('CASE 
                    WHEN balance = 0 THEN "0"
                    WHEN balance < 1000 THEN "1-999"
                    WHEN balance < 5000 THEN "1000-4999"
                    WHEN balance < 10000 THEN "5000-9999"
                    WHEN balance < 50000 THEN "10000-49999"
                    WHEN balance < 100000 THEN "50000-99999"
                    ELSE "100000+"
                END as range'),
                DB::raw('COUNT(*) as user_count'),
                DB::raw('SUM(balance) as total_balance')
            )
            ->groupBy('range')
            ->get();
        
        // 만료 예정 포인트
        $expiringPoints = DB::table('auth_point_transactions')
            ->select(
                DB::raw('DATE(expire_date) as date'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(amount) as amount')
            )
            ->where('type', 'earn')
            ->where('is_expired', false)
            ->whereNotNull('expire_date')
            ->whereBetween('expire_date', [now(), now()->addDays(90)])
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        return view('jiny-auth::admin.points.statistics', compact('dailyStats', 'actionStats', 'balanceDistribution', 'expiringPoints'));
    }
    
    /**
     * 만료 포인트 처리
     * 스케줄러에서 호출
     */
    public function processExpiredPoints()
    {
        DB::beginTransaction();
        
        try {
            // 만료 대상 포인트 조회
            $expiredTransactions = DB::table('auth_point_transactions')
                ->where('type', 'earn')
                ->where('is_expired', false)
                ->where('expire_date', '<=', now()->toDateString())
                ->get();
            
            foreach ($expiredTransactions as $transaction) {
                // 포인트 지갑 조회
                $pointWallet = DB::table('auth_user_points')
                    ->where('user_id', $transaction->user_id)
                    ->first();
                
                if ($pointWallet && $pointWallet->balance >= $transaction->amount) {
                    $newBalance = $pointWallet->balance - $transaction->amount;
                    
                    // 만료 거래 기록
                    DB::table('auth_point_transactions')->insert([
                        'user_id' => $transaction->user_id,
                        'type' => 'expire',
                        'action' => 'point_expiration',
                        'amount' => -$transaction->amount,
                        'balance_before' => $pointWallet->balance,
                        'balance_after' => $newBalance,
                        'description' => '포인트 만료 (원거래 ID: ' . $transaction->id . ')',
                        'reference_type' => 'point_transaction',
                        'reference_id' => $transaction->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    
                    // 포인트 잔액 업데이트
                    DB::table('auth_user_points')
                        ->where('user_id', $transaction->user_id)
                        ->update([
                            'balance' => $newBalance,
                            'total_expired' => DB::raw('total_expired + ' . $transaction->amount),
                            'updated_at' => now(),
                        ]);
                }
                
                // 만료 플래그 업데이트
                DB::table('auth_point_transactions')
                    ->where('id', $transaction->id)
                    ->update([
                        'is_expired' => true,
                        'updated_at' => now(),
                    ]);
            }
            
            DB::commit();
            
            return [
                'success' => true,
                'expired_count' => count($expiredTransactions),
                'message' => '만료 포인트 처리가 완료되었습니다.'
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            
            return [
                'success' => false,
                'message' => '만료 포인트 처리 실패: ' . $e->getMessage()
            ];
        }
    }
}