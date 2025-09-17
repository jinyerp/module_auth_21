<?php

namespace Jiny\Auth\App\Http\Controllers\User;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserEmoneyController extends Controller
{
    /**
     * eMoney 대시보드
     * GET /home/emoney
     */
    public function index(Request $request)
    {
        $userId = auth()->id();
        
        // eMoney 지갑 조회 또는 생성
        $wallet = $this->getOrCreateWallet($userId);
        
        // 최근 거래 내역
        $recentTransactions = DB::table('auth_emoney_transactions')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        // 이번 달 통계
        $monthlyStats = [
            'deposited' => DB::table('auth_emoney_transactions')
                ->where('user_id', $userId)
                ->where('type', 'deposit')
                ->where('status', 'completed')
                ->whereMonth('created_at', now()->month)
                ->sum('amount'),
            'withdrawn' => DB::table('auth_emoney_transactions')
                ->where('user_id', $userId)
                ->where('type', 'withdraw')
                ->where('status', 'completed')
                ->whereMonth('created_at', now()->month)
                ->sum('amount'),
            'spent' => DB::table('auth_emoney_transactions')
                ->where('user_id', $userId)
                ->where('type', 'payment')
                ->where('status', 'completed')
                ->whereMonth('created_at', now()->month)
                ->sum('amount'),
        ];
        
        return view('jiny-auth::user.emoney.index', compact('wallet', 'recentTransactions', 'monthlyStats'));
    }
    
    /**
     * 충전 페이지
     * GET /home/emoney/deposit
     */
    public function depositForm(Request $request)
    {
        $userId = auth()->id();
        $wallet = $this->getOrCreateWallet($userId);
        
        // 충전 방법
        $depositMethods = [
            'bank_transfer' => '무통장입금',
            'card' => '신용/체크카드',
            'virtual_account' => '가상계좌',
        ];
        
        // 최근 충전 내역
        $recentDeposits = DB::table('auth_deposit_logs')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        return view('jiny-auth::user.emoney.deposit', compact('wallet', 'depositMethods', 'recentDeposits'));
    }
    
    /**
     * 충전 처리
     * POST /home/emoney/deposit
     */
    public function deposit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1000|max:10000000',
            'method' => 'required|in:bank_transfer,card,virtual_account',
            'depositor_name' => 'required_if:method,bank_transfer|string|max:50',
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        $userId = auth()->id();
        $depositId = 'DEP' . date('YmdHis') . Str::random(6);
        
        DB::beginTransaction();
        
        try {
            // 충전 로그 생성
            DB::table('auth_deposit_logs')->insert([
                'user_id' => $userId,
                'deposit_id' => $depositId,
                'amount' => $request->amount,
                'fee' => 0, // 수수료 계산 로직 추가 가능
                'net_amount' => $request->amount,
                'currency' => 'KRW',
                'method' => $request->method,
                'status' => $request->method == 'bank_transfer' ? 'pending' : 'processing',
                'depositor_name' => $request->depositor_name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // 무통장입금인 경우 입금 안내
            if ($request->method == 'bank_transfer') {
                DB::commit();
                
                return redirect()->route('home.emoney.deposit.confirm', $depositId)
                    ->with('success', '충전 신청이 완료되었습니다. 입금 후 확인 처리됩니다.');
            }
            
            // 카드/가상계좌는 자동 처리 (실제로는 PG 연동 필요)
            $this->processDeposit($depositId);
            
            DB::commit();
            
            return redirect()->route('home.emoney')
                ->with('success', '충전이 완료되었습니다.');
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->with('error', '충전 처리 중 오류가 발생했습니다.')->withInput();
        }
    }
    
    /**
     * 출금 페이지
     * GET /home/emoney/withdraw
     */
    public function withdrawForm(Request $request)
    {
        $userId = auth()->id();
        $wallet = $this->getOrCreateWallet($userId);
        
        // 등록된 계좌
        $bankAccounts = DB::table('auth_bank_accounts')
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->orderBy('is_default', 'desc')
            ->get();
        
        // 출금 신청 내역
        $withdrawRequests = DB::table('auth_withdrawal_requests')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        return view('jiny-auth::user.emoney.withdraw', compact('wallet', 'bankAccounts', 'withdrawRequests'));
    }
    
    /**
     * 출금 신청
     * POST /home/emoney/withdraw
     */
    public function withdraw(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:10000',
            'bank_account_id' => 'required|exists:auth_bank_accounts,id',
            'password' => 'required',
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        // 비밀번호 확인
        if (!Hash::check($request->password, auth()->user()->password)) {
            return back()->with('error', '비밀번호가 일치하지 않습니다.')->withInput();
        }
        
        $userId = auth()->id();
        $wallet = $this->getOrCreateWallet($userId);
        
        // 잔액 확인
        if ($wallet->balance < $request->amount) {
            return back()->with('error', '출금 가능 금액이 부족합니다.')->withInput();
        }
        
        // 계좌 정보 확인
        $bankAccount = DB::table('auth_bank_accounts')
            ->where('id', $request->bank_account_id)
            ->where('user_id', $userId)
            ->first();
        
        if (!$bankAccount) {
            return back()->with('error', '유효하지 않은 계좌입니다.')->withInput();
        }
        
        $requestId = 'WD' . date('YmdHis') . Str::random(6);
        $fee = $this->calculateWithdrawalFee($request->amount);
        
        DB::beginTransaction();
        
        try {
            // 출금 신청 생성
            DB::table('auth_withdrawal_requests')->insert([
                'user_id' => $userId,
                'request_id' => $requestId,
                'amount' => $request->amount,
                'fee' => $fee,
                'net_amount' => $request->amount - $fee,
                'currency' => 'KRW',
                'bank_account_id' => $bankAccount->id,
                'bank_code' => $bankAccount->bank_code,
                'bank_name' => $bankAccount->bank_name,
                'account_number' => $bankAccount->account_number,
                'account_holder' => $bankAccount->account_holder,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // 지갑에서 금액 차감 (pending 상태로)
            DB::table('auth_emoney_wallets')
                ->where('user_id', $userId)
                ->update([
                    'pending_withdrawal' => DB::raw('pending_withdrawal + ' . $request->amount),
                    'updated_at' => now(),
                ]);
            
            DB::commit();
            
            return redirect()->route('home.emoney')
                ->with('success', '출금 신청이 완료되었습니다. 관리자 승인 후 처리됩니다.');
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->with('error', '출금 신청 중 오류가 발생했습니다.')->withInput();
        }
    }
    
    /**
     * 은행 계좌 목록
     * GET /home/emoney/bank
     */
    public function bankAccounts(Request $request)
    {
        $userId = auth()->id();
        
        $bankAccounts = DB::table('auth_bank_accounts')
            ->join('auth_banks', 'auth_bank_accounts.bank_code', '=', 'auth_banks.code')
            ->select(
                'auth_bank_accounts.*',
                'auth_banks.name as bank_name',
                'auth_banks.logo as bank_logo'
            )
            ->where('auth_bank_accounts.user_id', $userId)
            ->orderBy('auth_bank_accounts.is_default', 'desc')
            ->orderBy('auth_bank_accounts.created_at', 'desc')
            ->get();
        
        $banks = DB::table('auth_banks')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
        
        return view('jiny-auth::user.emoney.bank-accounts', compact('bankAccounts', 'banks'));
    }
    
    /**
     * 은행 계좌 등록
     * POST /home/emoney/bank
     */
    public function addBankAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bank_code' => 'required|exists:auth_banks,code',
            'account_number' => 'required|string|max:30',
            'account_holder' => 'required|string|max:50',
            'is_default' => 'boolean',
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        $userId = auth()->id();
        
        // 계좌 중복 확인
        $exists = DB::table('auth_bank_accounts')
            ->where('user_id', $userId)
            ->where('bank_code', $request->bank_code)
            ->where('account_number', $request->account_number)
            ->exists();
        
        if ($exists) {
            return back()->with('error', '이미 등록된 계좌입니다.')->withInput();
        }
        
        $bank = DB::table('auth_banks')->where('code', $request->bank_code)->first();
        
        DB::beginTransaction();
        
        try {
            // 기본 계좌 설정 시 기존 기본 계좌 해제
            if ($request->get('is_default')) {
                DB::table('auth_bank_accounts')
                    ->where('user_id', $userId)
                    ->update(['is_default' => false]);
            }
            
            // 계좌 등록
            DB::table('auth_bank_accounts')->insert([
                'user_id' => $userId,
                'bank_code' => $request->bank_code,
                'bank_name' => $bank->name,
                'account_number' => encrypt($request->account_number),
                'account_holder' => encrypt($request->account_holder),
                'is_default' => $request->get('is_default', false),
                'is_verified' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            DB::commit();
            
            return redirect()->route('home.emoney.bank')
                ->with('success', '계좌가 등록되었습니다.');
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->with('error', '계좌 등록 중 오류가 발생했습니다.')->withInput();
        }
    }
    
    /**
     * 은행 계좌 수정
     * PUT /home/emoney/bank/{id}
     */
    public function updateBankAccount(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'is_default' => 'boolean',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        $userId = auth()->id();
        
        $bankAccount = DB::table('auth_bank_accounts')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();
        
        if (!$bankAccount) {
            return response()->json([
                'success' => false,
                'message' => '계좌를 찾을 수 없습니다.'
            ], 404);
        }
        
        DB::beginTransaction();
        
        try {
            // 기본 계좌 설정 시 기존 기본 계좌 해제
            if ($request->get('is_default')) {
                DB::table('auth_bank_accounts')
                    ->where('user_id', $userId)
                    ->where('id', '!=', $id)
                    ->update(['is_default' => false]);
            }
            
            DB::table('auth_bank_accounts')
                ->where('id', $id)
                ->update([
                    'is_default' => $request->get('is_default', false),
                    'updated_at' => now(),
                ]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => '계좌 정보가 수정되었습니다.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => '계좌 수정 중 오류가 발생했습니다.'
            ], 500);
        }
    }
    
    /**
     * 은행 계좌 삭제
     * DELETE /home/emoney/bank/{id}
     */
    public function deleteBankAccount(Request $request, $id)
    {
        $userId = auth()->id();
        
        $bankAccount = DB::table('auth_bank_accounts')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();
        
        if (!$bankAccount) {
            return response()->json([
                'success' => false,
                'message' => '계좌를 찾을 수 없습니다.'
            ], 404);
        }
        
        // 출금 대기 중인 건이 있는지 확인
        $pendingWithdrawals = DB::table('auth_withdrawal_requests')
            ->where('bank_account_id', $id)
            ->whereIn('status', ['pending', 'approved', 'processing'])
            ->exists();
        
        if ($pendingWithdrawals) {
            return response()->json([
                'success' => false,
                'message' => '출금 처리 중인 계좌는 삭제할 수 없습니다.'
            ], 400);
        }
        
        DB::table('auth_bank_accounts')
            ->where('id', $id)
            ->delete();
        
        return response()->json([
            'success' => true,
            'message' => '계좌가 삭제되었습니다.'
        ]);
    }
    
    /**
     * eMoney 지갑 조회 또는 생성
     */
    private function getOrCreateWallet($userId)
    {
        $wallet = DB::table('auth_emoney_wallets')
            ->where('user_id', $userId)
            ->first();
        
        if (!$wallet) {
            DB::table('auth_emoney_wallets')->insert([
                'user_id' => $userId,
                'currency' => 'KRW',
                'balance' => 0,
                'total_deposited' => 0,
                'total_withdrawn' => 0,
                'total_spent' => 0,
                'pending_withdrawal' => 0,
                'is_active' => true,
                'is_locked' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            $wallet = DB::table('auth_emoney_wallets')
                ->where('user_id', $userId)
                ->first();
        }
        
        return $wallet;
    }
    
    /**
     * 충전 처리
     */
    private function processDeposit($depositId)
    {
        $deposit = DB::table('auth_deposit_logs')
            ->where('deposit_id', $depositId)
            ->first();
        
        if (!$deposit) {
            throw new \Exception('충전 정보를 찾을 수 없습니다.');
        }
        
        $wallet = $this->getOrCreateWallet($deposit->user_id);
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
        
        // 충전 로그 상태 업데이트
        DB::table('auth_deposit_logs')
            ->where('deposit_id', $depositId)
            ->update([
                'status' => 'confirmed',
                'transaction_id' => $transactionId,
                'confirmed_at' => now(),
                'updated_at' => now(),
            ]);
    }
    
    /**
     * 출금 수수료 계산
     */
    private function calculateWithdrawalFee($amount)
    {
        // 기본 수수료 1000원 또는 금액의 0.5% 중 큰 금액
        return max(1000, $amount * 0.005);
    }
}