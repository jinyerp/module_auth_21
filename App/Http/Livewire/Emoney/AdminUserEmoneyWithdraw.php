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
 * 회원 출금 관리 컴포넌트
 */
class AdminUserEmoneyWithdraw extends Component
{
    use WithPagination;
    public $actions =[];

    public $user_id;

    public $popupForm = false;
    public $popupDelete = false;
    public $popupConfirm = false;

    public $popupWindowWidth = "4xl";
    public $message = '';

    public $mode;
    public $forms = [];

    public $viewForm;

    public $search_keyword = '';

    public function mount()
    {
        if(!$this->viewForm) {
            $this->viewForm = 'jiny-users-emoney::admin.user_emoney_withdraw.form';
        }
    }

    public function render()
    {
        $db = DB::table('user_emoney_withdraw');

        if($this->user_id) {
            $db->where('user_id', $this->user_id);
        }

        if($this->search_keyword) {
            $db->where('description', 'like', '%'.$this->search_keyword.'%');
        }

        $rows = $db
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        return view('jiny-users-emoney::admin.user_emoney_withdraw.withdraw', [
            'rows' => $rows,
        ]);
    }

    // public function cancel()
    // {
    //     $this->popupForm = false;
    //     $this->forms = [];
    //     $this->mode = null;
    // }

    // public function deposit()
    // {
    //     $this->popupForm = true;
    //     $this->forms = [];
    //     $this->forms['type'] = 'deposit';
    //     $this->mode = 'deposit';

    //     if($this->user_id) {
    //         $this->forms['user_id'] = $this->user_id;
    //         $this->forms['email'] = DB::table('users')
    //             ->where('id', $this->user_id)
    //             ->value('email');
    //     }
    // }

    // public function storeDeposit()
    // {
    //     $user = DB::table('users')
    //         ->where('email', $this->forms['email'])
    //         ->first();
    //     $this->forms['user_id'] = $user->id; // 이메일을 사용자 id로 전환

    //     $row = DB::table('user_emoney_log')
    //         ->where('user_id', $user->id)
    //         ->orderBy('created_at', 'desc')
    //         ->first();
    //     if($row) {
    //         $this->forms['balance'] = $row->balance;
    //     } else {
    //         $this->forms['balance'] = 0; // 초기화
    //     }

    //     if(isset($this->forms['deposit'])) {
    //         $this->forms['balance'] += $this->forms['deposit'];
    //     }

    //     $this->forms['created_at'] = date('Y-m-d H:i:s');
    //     $this->forms['updated_at'] = date('Y-m-d H:i:s');
    //     //$this->forms['type'] = 'deposit';

    //     DB::table('user_emoney_log')->insert($this->forms);

    //     $this->cancel();
    // }

    // public function withdraw()
    // {
    //     $this->popupForm = true;
    //     $this->forms = [];
    //     $this->forms['type'] = 'withdraw';
    //     $this->mode = 'withdraw';

    //     if($this->user_id) {
    //         $this->forms['user_id'] = $this->user_id;
    //         $this->forms['email'] = DB::table('users')
    //             ->where('id', $this->user_id)
    //             ->value('email');
    //     }
    // }

    // public function storeWithdraw()
    // {
    //     $user = DB::table('users')
    //         ->where('email', $this->forms['email'])
    //         ->first();
    //     $this->forms['user_id'] = $user->id; // 이메일을 사용자 id로 전환

    //     $row = DB::table('user_emoney_log')
    //         ->where('user_id', $user->id)
    //         ->orderBy('created_at', 'desc')
    //         ->first();
    //     if($row) {
    //         $this->forms['balance'] = $row->balance;
    //     } else {
    //         $this->forms['balance'] = 0; // 초기화
    //     }

    //     if(isset($this->forms['withdraw'])) {
    //         $this->forms['balance'] -= $this->forms['withdraw'];
    //     }

    //     $this->forms['created_at'] = date('Y-m-d H:i:s');
    //     $this->forms['updated_at'] = date('Y-m-d H:i:s');
    //     //$this->forms['type'] = 'withdraw';

    //     DB::table('user_emoney_log')->insert($this->forms);

    //     $this->cancel();
    // }

    // public function edit($id)
    // {
    //     $row = DB::table('user_emoney_log')
    //         ->where('id', $id)
    //         ->first();
    //     $this->forms = get_object_vars($row);
    //     $this->mode = $this->forms['type'];

    //     $this->popupForm = true;
    // }

    // 입금 수정
    // public function update()
    // {
    //     $this->forms['updated_at'] = date('Y-m-d H:i:s');
    //     DB::table('user_emoney_log')
    //         ->where('id', $this->forms['id'])
    //         ->update($this->forms);

    //     // 이전 입금 금액 조회
    //     $row = DB::table('user_emoney_log')
    //         ->where('id', '<', $this->forms['id'])
    //         ->orderBy('id', 'desc')
    //         ->first();

    //     if($row) {
    //         $id = $row->id;
    //         $balance = $row->balance;

    //         // 금액 다시 계산
    //         $rows = DB::table('user_emoney_log')
    //         ->where('user_id', $this->forms['user_id'])
    //         ->where('id', '>', $id)
    //         ->orderBy('created_at', 'asc')
    //         ->get();
    //     } else {
    //         $id = $this->forms['id'];
    //         $balance = 0;

    //         // 금액 다시 계산
    //         $rows = DB::table('user_emoney_log')
    //         ->where('user_id', $this->forms['user_id'])
    //         ->where('id', '>=', $id)
    //         ->orderBy('created_at', 'asc')
    //         ->get();
    //     }

    //     $updates = [];
    //     foreach($rows as $item) {
    //         if(isset($item->deposit)) {
    //             $balance += $item->deposit;
    //         }
    //         if(isset($item->withdraw)) {
    //             $balance -= $item->withdraw;
    //         }
    //         $updates[] = [
    //             'id' => $item->id,
    //             'balance' => $balance
    //         ];
    //     }

    //     // 일괄 업데이트
    //     if(count($updates)) {
    //         DB::table('user_emoney_log')
    //             ->upsert($updates, ['id'], ['balance']);
    //     }

    //     $this->popupDelete = false;
    //     $this->cancel();
    // }



    // public function delete($id)
    // {
    //     $this->popupDelete = true;
    //     $this->forms['id'] = $id;
    // }

    // public function deleteConfirm()
    // {
    //     // 삭제 전 조회
    //     $row = DB::table('user_emoney_log')
    //         ->where('id', '<', $this->forms['id'])
    //         ->orderBy('id', 'desc')
    //         ->first();
    //     //dd($row);
    //     if($row) {
    //         $id = $row->id;
    //         $balance = $row->balance;
    //     } else {
    //         $id = $this->forms['id'];
    //         $balance = 0;
    //     }

    //     // 삭제
    //     DB::table('user_emoney_log')
    //         ->where('id', $this->forms['id'])
    //         ->delete();

    //     // 금액 다시 계산
    //     $rows = DB::table('user_emoney_log')
    //         ->where('user_id', $this->forms['user_id'])
    //         ->where('id', '>', $id)
    //         ->orderBy('created_at', 'asc')
    //         ->get();


    //     $updates = [];
    //     foreach($rows as $item) {
    //         if(isset($item->deposit)) {
    //             $balance += $item->deposit;
    //         }
    //         if(isset($item->withdraw)) {
    //             $balance -= $item->withdraw;
    //         }
    //         $updates[] = [
    //             'id' => $item->id,
    //             'balance' => $balance
    //         ];
    //     }

    //     // 일괄 업데이트
    //     if(count($updates)) {
    //         DB::table('user_emoney_log')
    //             ->upsert($updates, ['id'], ['balance']);
    //     }







    //     $this->popupDelete = false;
    //     $this->cancel();
    // }

    public function search()
    {
        $this->resetPage();
    }

    public function searchReset()
    {
        $this->search_keyword = '';
        $this->resetPage();
    }

    /**
     * 출금 승인
     */
    public function confirm($id)
    {
        // 출금 신청 정보 조회
        $row = DB::table('user_emoney_withdraw')
            ->where('id', $id)
            ->first();

        // 이전 잔액 조회
        $log = DB::table('user_emoney_log')
            ->where('user_id', $row->user_id)
            ->orderBy('created_at', 'desc')
            ->first();
        $balance = $log->balance ?? 0;
        $point = $log->point ?? 0;

        if(($balance-$point) >= $row->amount) {
            $description = '출금 승인, ';
            //$description .= $id.":user_emoney_withdraw";

            // 출금 로그 추가
            $log_id = DB::table('user_emoney_log')->insertGetId([
                'user_id' => $row->user_id,
                'email' => $row->email,
                'withdraw' => $row->amount,
                'balance' => $balance - $row->amount,
                'type' => 'cash', // 현금만 출금가능

                'trans' => 'user_emoney_withdraw',
                'trans_id' => $id,

                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                'description' => $description
            ]);

            // 출금 로그 성공
            if($log_id) {
                DB::table('user_emoney_withdraw')
                ->where('id', $id)
                ->update([
                    'checked' => 1,
                    'checked_at' => date('Y-m-d H:i:s'),
                    'status' => 'success',
                    'log_id' => $log_id
                ]);
            }
        } else {
            // 출금 로그
            $this->message = '잔액이 부족합니다.';
        }
    }

    /**
     * 출금 승인 취소
     */
    public function confirmCancel($id)
    {
        // 출금 신청 정보 조회
        $row = DB::table('user_emoney_withdraw')
            ->where('id', $id)
            ->first();

        // 이전 잔액 조회
        $log = DB::table('user_emoney_log')
            ->where('user_id', $row->user_id)
            ->orderBy('created_at', 'desc')
            ->first();
        $balance = $log->balance ?? 0;
        $point = $log->point ?? 0;


        $description = '출금 승인 취소, ';
        //$description .= $id.":user_emoney_withdraw";

        // 출금 로그 추가
        $log_id = DB::table('user_emoney_log')->insertGetId([
            'user_id' => $row->user_id,
            'email' => $row->email,
            'deposit' => $row->amount,
            'balance' => $balance + $row->amount,
            'type' => 'cash', // 현금만 출금가능

            'trans' => 'user_emoney_withdraw',
            'trans_id' => $id,

            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'description' => $description
        ]);

        if($log_id) {
            DB::table('user_emoney_withdraw')
            ->where('id', $id)
            ->update([
                'checked' => null,
                'checked_at' => date('Y-m-d H:i:s'),
                'status' => 'cancel',
                'log_id' => $log_id
            ]);
        }

    }

    public function cancel()
    {
        $this->popupForm = false;
        $this->forms = [];
    }

    public function edit($id)
    {
        $this->popupForm = true;

        $row = DB::table('user_emoney_withdraw')
            ->where('id', $id)
            ->first();
        $this->forms = get_object_vars($row);
    }

    public function update()
    {
        if($this->forms['log_id']) {
            $this->message = "출금 로그 수정 불가, 원본 동작으로 취소해 주세요.";
        } else {
            $this->popupForm = false;
            $this->popupForm = false;
        }
        //dd($this->forms);
    }

    public function delete($id)
    {
        $this->popupDelete = true;
        $this->forms['id'] = $id;
    }

    public function deleteConfirm()
    {
        //dd($this->forms);


        // 로그기록 삭제
        //$id = $this->forms['id'];
        DB::table('user_emoney_log')
            ->where('trans', 'user_emoney_withdraw')
            ->where('trans_id', $this->forms['id'])
            ->delete();

        // 출금신청 삭제
        DB::table('user_emoney_withdraw')
            ->where('id', $this->forms['id'])
            ->delete();

        $this->popupDelete = false;
        $this->cancel();
    }

}
