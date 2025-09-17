<?php

namespace Jiny\Auth\App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class TwoFactorController extends Controller
{
    private $google2fa;

    public function __construct()
    {
        $this->middleware('auth');
        $this->google2fa = new Google2FA();
    }

    /**
     * 2FA 설정 페이지
     */
    public function setup()
    {
        $user = Auth::user();
        
        // 이미 2FA가 활성화된 경우
        if ($user->two_factor_secret) {
            return redirect()->route('profile')
                ->with('info', '이미 2단계 인증이 활성화되어 있습니다.');
        }

        // 시크릿 키 생성
        $secret = $this->google2fa->generateSecretKey();
        
        // 임시로 세션에 저장
        Session::put('2fa_secret', $secret);
        
        // QR 코드 URL 생성
        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );
        
        // QR 코드 이미지 생성
        $writer = new Writer(
            new ImageRenderer(
                new RendererStyle(400),
                new ImagickImageBackEnd()
            )
        );
        
        $qrCode = base64_encode($writer->writeString($qrCodeUrl));
        
        return view('jiny-auth::auth.two-factor-setup', [
            'secret' => $secret,
            'qrCode' => $qrCode,
        ]);
    }

    /**
     * 2FA 활성화
     */
    public function enable(Request $request)
    {
        $request->validate([
            'code' => 'required|digits:6',
        ]);
        
        $secret = Session::get('2fa_secret');
        
        if (!$secret) {
            return redirect()->route('2fa.setup')
                ->withErrors(['code' => '세션이 만료되었습니다. 다시 시도해주세요.']);
        }
        
        // 코드 검증
        $valid = $this->google2fa->verifyKey($secret, $request->code);
        
        if (!$valid) {
            return back()->withErrors(['code' => '인증 코드가 올바르지 않습니다.']);
        }
        
        // 2FA 활성화
        $user = Auth::user();
        $user->update([
            'two_factor_secret' => encrypt($secret),
            'two_factor_enabled' => true,
        ]);
        
        // 복구 코드 생성
        $recoveryCodes = $this->generateRecoveryCodes();
        DB::table('two_factor_recovery_codes')->insert(
            array_map(function ($code) use ($user) {
                return [
                    'user_id' => $user->id,
                    'code' => bcrypt($code),
                    'created_at' => now(),
                ];
            }, $recoveryCodes)
        );
        
        // 세션 정리
        Session::forget('2fa_secret');
        
        return view('jiny-auth::auth.two-factor-recovery', [
            'recoveryCodes' => $recoveryCodes,
        ]);
    }

    /**
     * 2FA 인증 페이지
     */
    public function challenge()
    {
        return view('jiny-auth::auth.two-factor-challenge');
    }

    /**
     * 2FA 인증 처리
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required',
        ]);
        
        $user = Auth::user();
        
        // 일반 코드 확인
        if (strlen($request->code) === 6) {
            $secret = decrypt($user->two_factor_secret);
            $valid = $this->google2fa->verifyKey($secret, $request->code);
            
            if ($valid) {
                Session::put('2fa_verified', true);
                return redirect()->intended('/home');
            }
        }
        
        // 복구 코드 확인
        $recoveryCodes = DB::table('two_factor_recovery_codes')
            ->where('user_id', $user->id)
            ->whereNull('used_at')
            ->get();
        
        foreach ($recoveryCodes as $recoveryCode) {
            if (password_verify($request->code, $recoveryCode->code)) {
                // 복구 코드 사용 처리
                DB::table('two_factor_recovery_codes')
                    ->where('id', $recoveryCode->id)
                    ->update(['used_at' => now()]);
                
                Session::put('2fa_verified', true);
                return redirect()->intended('/home')
                    ->with('warning', '복구 코드를 사용했습니다. 보안을 위해 새로운 복구 코드를 생성하세요.');
            }
        }
        
        return back()->withErrors(['code' => '인증 코드가 올바르지 않습니다.']);
    }

    /**
     * 2FA 비활성화
     */
    public function disable(Request $request)
    {
        $request->validate([
            'password' => 'required|current_password',
        ]);
        
        $user = Auth::user();
        $user->update([
            'two_factor_secret' => null,
            'two_factor_enabled' => false,
        ]);
        
        // 복구 코드 삭제
        DB::table('two_factor_recovery_codes')
            ->where('user_id', $user->id)
            ->delete();
        
        return redirect()->route('profile')
            ->with('success', '2단계 인증이 비활성화되었습니다.');
    }

    /**
     * 백업 코드 조회
     * GET /home/account/2fa/backup-codes
     */
    public function backupCodes()
    {
        $user = Auth::user();
        
        // 2FA가 활성화되지 않은 경우
        if (!$user->two_factor_secret) {
            return redirect()->route('profile')
                ->with('error', '2단계 인증이 활성화되어 있지 않습니다.');
        }
        
        // 사용되지 않은 백업 코드만 조회 (암호화되어 있어 실제 코드는 보여줄 수 없음)
        $backupCodesCount = DB::table('two_factor_recovery_codes')
            ->where('user_id', $user->id)
            ->whereNull('used_at')
            ->count();
        
        return view('jiny-auth::auth.two-factor-backup-codes', [
            'backupCodesCount' => $backupCodesCount
        ]);
    }
    
    /**
     * 복구 코드 재생성
     */
    public function regenerateRecoveryCodes(Request $request)
    {
        $request->validate([
            'password' => 'required|current_password',
        ]);
        
        $user = Auth::user();
        
        // 기존 복구 코드 삭제
        DB::table('two_factor_recovery_codes')
            ->where('user_id', $user->id)
            ->delete();
        
        // 새 복구 코드 생성
        $recoveryCodes = $this->generateRecoveryCodes();
        DB::table('two_factor_recovery_codes')->insert(
            array_map(function ($code) use ($user) {
                return [
                    'user_id' => $user->id,
                    'code' => bcrypt($code),
                    'created_at' => now(),
                ];
            }, $recoveryCodes)
        );
        
        return view('jiny-auth::auth.two-factor-recovery', [
            'recoveryCodes' => $recoveryCodes,
        ]);
    }

    /**
     * 복구 코드 생성
     */
    private function generateRecoveryCodes($count = 8)
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = strtoupper(bin2hex(random_bytes(4))) . '-' . strtoupper(bin2hex(random_bytes(4)));
        }
        return $codes;
    }
}