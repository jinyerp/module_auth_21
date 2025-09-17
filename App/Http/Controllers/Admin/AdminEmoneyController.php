<?php

namespace Jiny\Auth\App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Str;

class AdminEmoneyController extends Controller
{
    /**
     * eMoney 관리 대시보드
     * GET /admin/auth/emoney
     */
    public function index(Request $request)
    {
        // 전체 통계
        $stats = [
            'total_balance' => DB::table('auth_emoney_wallets')->sum('balance'),
            'total_deposited' => DB::table('auth_emoney_wallets')->sum('total_deposited'),
            'total_withdrawn' => DB::table('auth_emoney_wallets')->sum('total_withdrawn'),
            'total_spent' => DB::table('auth_emoney_wallets')->sum('total_spent'),
            'pending_withdrawals' => DB::table('auth_withdrawal_requests')
                ->where('status', 'pending')
                ->sum('amount'),
            'pending_deposits' => DB::table('auth_deposit_logs')
                ->where('status', 'pending')
                ->sum('amount'),
        ];
        
        // 최근 거래
        $recentTransactions = DB::table('auth_emoney_transactions')
            ->join('users', 'auth_emoney_transactions.user_id', '=', 'users.id')
            ->select(
                'auth_emoney_transactions.*',
                'users.name as user_name',
                'users.email as user_email'
            )
            ->orderBy('auth_emoney_transactions.created_at', 'desc')
            ->limit(10)
            ->get();
        
        // 대기 중인 출금 신청
        $pendingWithdrawals = DB::table('auth_withdrawal_requests')
            ->join('users', 'auth_withdrawal_requests.user_id', '=', 'users.id')
            ->select(
                'auth_withdrawal_requests.*',
                'users.name as user_name',
                'users.email as user_email'
            )
            ->where('auth_withdrawal_requests.status', 'pending')
            ->orderBy('auth_withdrawal_requests.created_at')
            ->limit(5)
            ->get();
        
        // 대기 중인 입금 확인
        $pendingDeposits = DB::table('auth_deposit_logs')
            ->join('users', 'auth_deposit_logs.user_id', '=', 'users.id')
            ->select(
                'auth_deposit_logs.*',
                'users.name as user_name',
                'users.email as user_email'
            )
            ->where('auth_deposit_logs.status', 'pending')
            ->orderBy('auth_deposit_logs.created_at')
            ->limit(5)
            ->get();
        
        return view('jiny-auth::admin.emoney.dashboard', compact('stats', 'recentTransactions', 'pendingWithdrawals', 'pendingDeposits'));
    }
    
    /**
     * 사용자 eMoney 목록
     * GET /admin/auth/emoney/user
     */
    public function userList(Request $request)
    {
        $query = DB::table('auth_emoney_wallets')
            ->join('users', 'auth_emoney_wallets.user_id', '=', 'users.id')
            ->select(
                'auth_emoney_wallets.*',
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
            $query->where('auth_emoney_wallets.balance', '>=', $request->get('min_balance'));
        }
        
        if ($request->has('is_locked')) {
            $query->where('auth_emoney_wallets.is_locked', $request->get('is_locked') === 'true');
        }
        
        $wallets = $query->orderBy('auth_emoney_wallets.balance', 'desc')
            ->paginate(20);
        
        return view('jiny-auth::admin.emoney.user-list', compact('wallets'));
    }
    
    /**
     * 사용자 eMoney 내역
     * GET /admin/auth/emoney/log/{userId}
     */
    public function userLog(Request $request, $userId)
    {
        $user = User::find($userId);
        if (!$user) {
            return redirect()->route('admin.auth.emoney.user')
                ->with('error', '사용자를 찾을 수 없습니다.');
        }
        
        // eMoney 지갑
        $wallet = DB::table('auth_emoney_wallets')
            ->where('user_id', $userId)
            ->first();
        
        if (!$wallet) {
            $wallet = (object)[
                'balance' => 0,
                'total_deposited' => 0,
                'total_withdrawn' => 0,
                'total_spent' => 0,
                'pending_withdrawal' => 0,
            ];
        }
        
        // 거래 내역
        $query = DB::table('auth_emoney_transactions')
            ->where('user_id', $userId);
        
        // 필터
        if ($request->has('type')) {
            $query->where('type', $request->get('type'));
        }
        
        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }
        
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->get('date_from'));
        }
        
        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->get('date_to'));
        }
        
        $transactions = $query->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('jiny-auth::admin.emoney.user-log', compact('user', 'wallet', 'transactions'));
    }
    
    /**
     * 사용자 은행계좌 관리
     * GET /admin/auth/emoney/bank/{userId}
     */
    public function userBankAccounts(Request $request, $userId)
    {
        $user = User::find($userId);
        if (!$user) {
            return redirect()->route('admin.auth.emoney.user')
                ->with('error', '사용자를 찾을 수 없습니다.');
        }
        
        $bankAccounts = DB::table('auth_bank_accounts')
            ->join('auth_banks', 'auth_bank_accounts.bank_code', '=', 'auth_banks.code')
            ->select(
                'auth_bank_accounts.*',
                'auth_banks.name as bank_name'
            )
            ->where('auth_bank_accounts.user_id', $userId)
            ->orderBy('auth_bank_accounts.is_default', 'desc')
            ->orderBy('auth_bank_accounts.created_at', 'desc')
            ->get();
        
        // 복호화
        foreach ($bankAccounts as $account) {
            $account->account_number_decrypted = decrypt($account->account_number);
            $account->account_holder_decrypted = decrypt($account->account_holder);
        }
        
        return view('jiny-auth::admin.emoney.user-bank', compact('user', 'bankAccounts'));
    }
    
    /**
     * 출금 신청 관리
     * GET /admin/auth/emoney/withdraw/{id}
     */
    public function withdrawalDetail(Request $request, $id)
    {
        $withdrawal = DB::table('auth_withdrawal_requests')
            ->join('users', 'auth_withdrawal_requests.user_id', '=', 'users.id')
            ->leftJoin('auth_bank_accounts', 'auth_withdrawal_requests.bank_account_id', '=', 'auth_bank_accounts.id')
            ->select(
                'auth_withdrawal_requests.*',
                'users.name as user_name',
                'users.email as user_email',
                'auth_bank_accounts.account_number as encrypted_account_number',
                'auth_bank_accounts.account_holder as encrypted_account_holder'
            )
            ->where('auth_withdrawal_requests.id', $id)
            ->first();
        
        if (!$withdrawal) {
            return redirect()->route('admin.auth.emoney')
                ->with('error', '출금 신청을 찾을 수 없습니다.');
        }
        
        // 복호화
        if ($withdrawal->encrypted_account_number) {
            $withdrawal->account_number = decrypt($withdrawal->encrypted_account_number);
            $withdrawal->account_holder = decrypt($withdrawal->encrypted_account_holder);
        }
        
        // 사용자 지갑 정보
        $wallet = DB::table('auth_emoney_wallets')
            ->where('user_id', $withdrawal->user_id)
            ->first();
        
        return view('jiny-auth::admin.emoney.withdrawal-detail', compact('withdrawal', 'wallet'));
    }
    
    /**
     * 출금 승인
     * POST /admin/auth/emoney/withdraw/{id}/approve
     */
    public function approveWithdrawal(Request $request, $id)
    {
        $withdrawal = DB::table('auth_withdrawal_requests')
            ->where('id', $id)
            ->where('status', 'pending')
            ->first();
        
        if (!$withdrawal) {
            return response()->json([
                'success' => false,
                'message' => '출금 신청을 찾을 수 없거나 이미 처리되었습니다.'
            ], 404);
        }
        
        $wallet = DB::table('auth_emoney_wallets')
            ->where('user_id', $withdrawal->user_id)
            ->first();
        
        // 잔액 확인
        if ($wallet->balance < $withdrawal->amount) {
            return response()->json([
                'success' => false,
                'message' => '사용자의 잔액이 부족합니다.'
            ], 400);
        }
        
        DB::beginTransaction();
        
        try {
            $transactionId = 'TRX' . date('YmdHis') . Str::random(6);
            
            // 출금 승인
            DB::table('auth_withdrawal_requests')
                ->where('id', $id)
                ->update([
                    'status' => 'approved',
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                    'admin_note' => $request->note,
                    'updated_at' => now(),
                ]);
            
            // eMoney 거래 기록
            DB::table('auth_emoney_transactions')->insert([
                'user_id' => $withdrawal->user_id,
                'transaction_id' => $transactionId,
                'type' => 'withdraw',
                'method' => 'bank_transfer',
                'amount' => $withdrawal->amount,
                'fee' => $withdrawal->fee,
                'net_amount' => $withdrawal->net_amount,
                'balance_before' => $wallet->balance,
                'balance_after' => $wallet->balance - $withdrawal->amount,
                'currency' => $withdrawal->currency,
                'status' => 'processing',
                'description' => 'eMoney 출금',
                'reference_type' => 'withdrawal',
                'reference_id' => $withdrawal->id,
                'processed_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // 지갑 잔액 업데이트
            DB::table('auth_emoney_wallets')
                ->where('user_id', $withdrawal->user_id)
                ->update([
                    'balance' => DB::raw('balance - ' . $withdrawal->amount),
                    'pending_withdrawal' => DB::raw('pending_withdrawal - ' . $withdrawal->amount),
                    'total_withdrawn' => DB::raw('total_withdrawn + ' . $withdrawal->net_amount),
                    'last_withdrawal_at' => now(),
                    'last_transaction_at' => now(),
                    'updated_at' => now(),
                ]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => '출금이 승인되었습니다.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => '출금 승인 중 오류가 발생했습니다.'
            ], 500);
        }
    }
    
    /**
     * 출금 거부
     * POST /admin/auth/emoney/withdraw/{id}/reject
     */
    public function rejectWithdrawal(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:500',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        $withdrawal = DB::table('auth_withdrawal_requests')
            ->where('id', $id)
            ->where('status', 'pending')
            ->first();
        
        if (!$withdrawal) {
            return response()->json([
                'success' => false,
                'message' => '출금 신청을 찾을 수 없거나 이미 처리되었습니다.'
            ], 404);
        }
        
        DB::beginTransaction();
        
        try {
            // 출금 거부
            DB::table('auth_withdrawal_requests')
                ->where('id', $id)
                ->update([
                    'status' => 'rejected',
                    'reject_reason' => $request->reason,
                    'admin_note' => $request->note,
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                    'updated_at' => now(),
                ]);
            
            // pending_withdrawal 금액 복구
            DB::table('auth_emoney_wallets')
                ->where('user_id', $withdrawal->user_id)
                ->update([
                    'pending_withdrawal' => DB::raw('pending_withdrawal - ' . $withdrawal->amount),
                    'updated_at' => now(),
                ]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => '출금이 거부되었습니다.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => '출금 거부 중 오류가 발생했습니다.'
            ], 500);
        }
    }
    
    /**
     * 입금 내역 관리
     * GET /admin/auth/emoney/deposit/{id}
     */
    public function depositDetail(Request $request, $id)
    {
        $deposit = DB::table('auth_deposit_logs')
            ->join('users', 'auth_deposit_logs.user_id', '=', 'users.id')
            ->select(
                'auth_deposit_logs.*',
                'users.name as user_name',
                'users.email as user_email'
            )
            ->where('auth_deposit_logs.id', $id)
            ->first();
        
        if (!$deposit) {
            return redirect()->route('admin.auth.emoney')
                ->with('error', '입금 내역을 찾을 수 없습니다.');
        }
        
        $deposit->payment_info = json_decode($deposit->payment_info, true) ?? [];
        
        return view('jiny-auth::admin.emoney.deposit-detail', compact('deposit'));
    }
    
    /**
     * 입금 확인
     * POST /admin/auth/emoney/deposit/{id}/confirm
     */
    public function confirmDeposit(Request $request, $id)
    {
        $deposit = DB::table('auth_deposit_logs')
            ->where('id', $id)
            ->where('status', 'pending')
            ->first();
        
        if (!$deposit) {
            return response()->json([
                'success' => false,
                'message' => '입금 내역을 찾을 수 없거나 이미 처리되었습니다.'
            ], 404);
        }
        
        DB::beginTransaction();
        
        try {
            $wallet = DB::table('auth_emoney_wallets')
                ->where('user_id', $deposit->user_id)
                ->first();
            
            if (!$wallet) {
                // 지갑 생성
                DB::table('auth_emoney_wallets')->insert([
                    'user_id' => $deposit->user_id,
                    'currency' => 'KRW',
                    'balance' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                $wallet = DB::table('auth_emoney_wallets')
                    ->where('user_id', $deposit->user_id)
                    ->first();
            }
            
            $transactionId = 'TRX' . date('YmdHis') . Str::random(6);
            
            // eMoney 거래 기록
            DB::table('auth_emoney_transactions')->insert([
                'user_id' => $deposit->user_id,
                'transaction_id' => $transactionId,
                'type' => 'deposit',
                'method' => $deposit->method,
                'amount' => $deposit->amount,
                'fee' => $deposit->fee,
                'net_amount' => $deposit->net_amount,
                'balance_before' => $wallet->balance,
                'balance_after' => $wallet->balance + $deposit->net_amount,
                'currency' => $deposit->currency,
                'status' => 'completed',
                'description' => 'eMoney 충전',
                'reference_type' => 'deposit',
                'reference_id' => $deposit->id,
                'processed_at' => now(),
                'completed_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // 지갑 잔액 업데이트
            DB::table('auth_emoney_wallets')
                ->where('user_id', $deposit->user_id)
                ->update([
                    'balance' => DB::raw('balance + ' . $deposit->net_amount),
                    'total_deposited' => DB::raw('total_deposited + ' . $deposit->net_amount),
                    'last_deposit_at' => now(),
                    'last_transaction_at' => now(),
                    'updated_at' => now(),
                ]);
            
            // 입금 로그 상태 업데이트
            DB::table('auth_deposit_logs')
                ->where('id', $id)
                ->update([
                    'status' => 'confirmed',
                    'transaction_id' => $transactionId,
                    'confirmed_by' => auth()->id(),
                    'confirmed_at' => now(),
                    'admin_note' => $request->note,
                    'updated_at' => now(),
                ]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => '입금이 확인되었습니다.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => '입금 확인 중 오류가 발생했습니다.'
            ], 500);
        }
    }
    
    /**
     * 은행 목록 관리
     * GET /admin/auth/bank
     */
    public function bankList(Request $request)
    {
        $banks = DB::table('auth_banks')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20);
        
        return view('jiny-auth::admin.emoney.bank-list', compact('banks'));
    }
    
    /**
     * 통화 목록 관리
     * GET /admin/auth/currency
     */
    public function currencyList(Request $request)
    {
        $currencies = DB::table('auth_currencies')
            ->orderBy('code')
            ->paginate(20);
        
        return view('jiny-auth::admin.emoney.currency-list', compact('currencies'));
    }
    
    /**
     * 통화 로그 관리
     * GET /admin/auth/currency/log/{code}
     */
    public function currencyLog(Request $request, $code)
    {
        $currency = DB::table('auth_currencies')
            ->where('code', $code)
            ->first();
        
        if (!$currency) {
            return redirect()->route('admin.auth.currency')
                ->with('error', '통화를 찾을 수 없습니다.');
        }
        
        $logs = DB::table('auth_currency_logs')
            ->leftJoin('users', 'auth_currency_logs.updated_by', '=', 'users.id')
            ->select(
                'auth_currency_logs.*',
                'users.name as updated_by_name'
            )
            ->where('auth_currency_logs.currency_code', $code)
            ->orderBy('auth_currency_logs.rate_date', 'desc')
            ->paginate(20);
        
        return view('jiny-auth::admin.emoney.currency-log', compact('currency', 'logs'));
    }
}