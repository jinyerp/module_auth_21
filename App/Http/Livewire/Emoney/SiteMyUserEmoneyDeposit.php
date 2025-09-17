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
class SiteMyUserEmoneyDeposit extends Component
{
    use WithPagination;
    public $user_id;

    public $bank_id;
    public $amount;

    public $type = "bank";
    public $card_type = "card";

    public $message;

    public function render()
    {
        $deposit = DB::table("user_emoney_deposit")
            ->where('user_id', Auth::user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('jiny-users-emoney::home.emoney_deposit.deposit',[
            'deposit'=>$deposit,
            //'emoney'=>$emoney
        ]);
    }

    public function deposit()
    {
        if(!$this->bank_id) {
            $this->message = "은행을 선택해주세요.";
            return;
        }

        if(!$this->amount) {
            $this->message = "입금금액을 입력해주세요.";
            return;
        }

        $bank = DB::table("auth_bank")
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

        DB::table("user_emoney_deposit")->insert($forms);

        $this->message = "입금요청이 완료되었습니다.";
        $this->bank_id = null;
        $this->amount = null;
    }

    public function cancel($id)
    {
        DB::table("user_emoney_deposit")
            ->where('id', $id)
            ->delete();
        $this->message = "입금요청이 취소되었습니다.";
    }
}
