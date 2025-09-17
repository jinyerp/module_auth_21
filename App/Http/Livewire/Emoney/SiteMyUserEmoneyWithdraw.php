<?php
namespace Jiny\Auth\App\Http\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Stichoza\GoogleTranslate\GoogleTranslate;
use Livewire\Attributes\On;
use Livewire\WithPagination;

/**
 * 회원 적립금 관리 컴포넌트
 */
class SiteMyUserEmoneyWithdraw extends Component
{
    use WithPagination;
    public $user_id;

    public $bank_id;
    public $amount;

    public $deposit;
    public $point;
    public $pending;
    //public $balance;

    public $message;

    public function render()
    {
        $emoney = DB::table("user_emoney_log")
            ->where('user_id', Auth::user()->id)
            ->orderBy('created_at', 'desc')
            ->first();
        if($emoney) {
            $this->deposit = $emoney->balance;
        } else {
            $this->deposit = 0;
        }

        // 적립된 포인트 금액
        if($emoney) {
            // $this->point = DB::table("user_emoney_log")
            //     ->where('user_id', Auth::user()->id)
            //     ->where('type', 'point')
            //     ->sum(DB::raw('COALESCE(deposit,0) - COALESCE(withdraw,0)'));

            $this->point = $emoney->point;
        } else {
            $this->point = 0;
        }


        // 미처리 금액
        $this->pending = DB::table("user_emoney_withdraw")
            ->where('user_id', Auth::user()->id)
            ->whereNull('checked')
            ->sum('amount');

        $withdraw = DB::table("user_emoney_withdraw")
            ->where('user_id', Auth::user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('jiny-users-emoney::home.emoney_withdraw.withdraw',[
            'withdraw'=>$withdraw,
            //'emoney'=>$emoney
        ]);
    }

    public function withdraw()
    {
        if(!$this->bank_id) {
            $this->message = "은행을 선택해주세요.";
            return;
        }

        if(!$this->amount) {
            $this->message = "출금금액을 입력해주세요.";
            return;
        }

        $balance = $this->deposit - $this->pending;
        if($balance < $this->amount) {
            $this->message = "출금 가능한 금액을 초과하였습니다.";
            return;
        }

        $bank = DB::table("user_emoney_bank")
            ->where('id', $this->bank_id)
            ->first();
        $forms = [];

        $forms['user_id'] = Auth::user()->id;
        $forms['email'] = Auth::user()->email;

        $forms['bank_id'] = $bank->id;
        $forms['bank'] = $bank->bank;
        $forms['account'] = $bank->account;
        $forms['owner'] = $bank->owner;
        $forms['amount'] = $this->amount;

        $forms['created_at'] = date('Y-m-d H:i:s');
        $forms['updated_at'] = date('Y-m-d H:i:s');

        $forms['status'] = 'pending';

        DB::table("user_emoney_withdraw")->insert($forms);

        $this->message = "출금요청이 완료되었습니다.";
        $this->bank_id = null;
        $this->amount = null;
    }

    public function cancel($id)
    {
        DB::table("user_emoney_withdraw")->where('id', $id)->delete();
        $this->message = "출금요청이 취소되었습니다.";
    }
}
