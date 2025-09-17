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
class AdminUserEmoney extends Component
{
    use WithPagination;

    public $user_id;

    public $popupForm = false;
    public $popupDelete = false;
    public $popupConfirm = false;

    public $popupWindowWidth = "4xl";
    public $message = '';

    public $mode;
    public $forms = [];

    public $viewForm = 'jiny-users-emoney::admin.user_emoney.form';

    public $search_keyword = '';

    public function render()
    {
        $db = DB::table('user_emoney_log');

        if($this->user_id) {
            $db->where('user_id', $this->user_id);
        }

        if($this->search_keyword) {
            $db->where('description', 'like', '%'.$this->search_keyword.'%');
        }

        $rows = $db
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        return view('jiny-users-emoney::admin.user_emoney.log', [
            'rows' => $rows,
        ]);
    }

    public function cancel()
    {
        $this->popupForm = false;
        $this->forms = [];
        $this->mode = null;
    }

    /**
     * 입금
     */
    public function deposit()
    {
        $this->popupForm = true;
        $this->forms = [];
        //$this->forms['type'] = 'deposit';
        $this->mode = 'deposit';

        if($this->user_id) {
            $this->forms['user_id'] = $this->user_id;
            $this->forms['email'] = DB::table('users')
                ->where('id', $this->user_id)
                ->value('email');
        }
    }

    /**
     * 입금 저장
     */
    public function storeDeposit()
    {
        $user = DB::table('users')
            ->where('email', $this->forms['email'])
            ->first();
        $this->forms['user_id'] = $user->id; // 이메일을 사용자 id로 전환

        $row = DB::table('user_emoney_log')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->first();
        if($row) {
            $this->forms['balance'] = $row->balance;
            $this->forms['point'] = $row->point;
        } else {
            $this->forms['balance'] = 0; // 초기화
            $this->forms['point'] = 0; // 초기화
        }

        if(isset($this->forms['deposit'])) {
            $this->forms['balance'] += $this->forms['deposit'];

            if(isset($this->forms['type']) && $this->forms['type'] == 'point') {
                $this->forms['point'] += $this->forms['deposit'];
            }
        }

        $this->forms['created_at'] = date('Y-m-d H:i:s');
        $this->forms['updated_at'] = date('Y-m-d H:i:s');
        //$this->forms['type'] = 'deposit';

        DB::table('user_emoney_log')->insert($this->forms);

        // user_emoney 테이블 업데이트
        user_emoney_deposit(
            $this->forms['email'],
            $this->forms['deposit']);

        $this->cancel();
    }




    /**
     * 출금
     */
    public function withdraw()
    {
        $this->popupForm = true;
        $this->forms = [];
        //$this->forms['type'] = 'withdraw';
        $this->mode = 'withdraw';

        if($this->user_id) {
            $this->forms['user_id'] = $this->user_id;
            $this->forms['email'] = DB::table('users')
                ->where('id', $this->user_id)
                ->value('email');
        }
    }

    /**
     * 출금 저장
     */
    public function storeWithdraw()
    {
        $user = DB::table('users')
            ->where('email', $this->forms['email'])
            ->first();
        $this->forms['user_id'] = $user->id; // 이메일을 사용자 id로 전환

        $row = DB::table('user_emoney_log')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->first();
        if($row) {
            $this->forms['balance'] = $row->balance;
            $this->forms['point'] = $row->point;
        } else {
            $this->forms['balance'] = 0; // 초기화
            $this->forms['point'] = 0; // 초기화
        }

        if(isset($this->forms['withdraw'])) {
            $this->forms['balance'] -= $this->forms['withdraw'];

            if(isset($this->forms['type']) && $this->forms['type'] == 'point') {
                $this->forms['point'] -= $this->forms['withdraw'];
            }
        }

        $this->forms['created_at'] = date('Y-m-d H:i:s');
        $this->forms['updated_at'] = date('Y-m-d H:i:s');
        //$this->forms['type'] = 'withdraw';

        DB::table('user_emoney_log')->insert($this->forms);

        // user_emoney 테이블 업데이트
        user_emoney_withdraw(
            $this->forms['email'],
            $this->forms['withdraw']);

        $this->cancel();
    }

    /**
     * 수정
     */
    public function edit($id)
    {
        $row = DB::table('user_emoney_log')
            ->where('id', $id)
            ->first();
        $this->forms = get_object_vars($row);
        $this->mode = $this->forms['type'];

        $this->popupForm = true;
    }

    /**
     * 입금 수정
     */
    public function update()
    {
        $this->forms['updated_at'] = date('Y-m-d H:i:s');
        DB::table('user_emoney_log')
            ->where('id', $this->forms['id'])
            ->update($this->forms);

        // 이전 입금 금액 조회
        $row = DB::table('user_emoney_log')
            ->where('id', '<', $this->forms['id'])
            ->orderBy('id', 'desc')
            ->first();

        if($row) {
            $id = $row->id;
            $balance = $row->balance;
            $point = $row->point;

            // 금액 다시 계산
            $rows = DB::table('user_emoney_log')
            ->where('user_id', $this->forms['user_id'])
            ->where('id', '>', $id)
            ->orderBy('created_at', 'asc')
            ->get();

        } else {
            $id = $this->forms['id'];
            $balance = 0;
            $point = 0;

            // 금액 다시 계산
            $rows = DB::table('user_emoney_log')
            ->where('user_id', $this->forms['user_id'])
            ->where('id', '>=', $id)
            ->orderBy('created_at', 'asc')
            ->get();
        }

        $updates = [];
        foreach($rows as $item) {
            if(isset($item->deposit)) {
                $balance += $item->deposit;

                if(isset($item->type) && $item->type == 'point') {
                    $point += $item->deposit;
                }
            }

            if(isset($item->withdraw)) {
                $balance -= $item->withdraw;

                if(isset($item->type) && $item->type == 'point') {
                    $point -= $item->withdraw;
                }
            }

            $updates[] = [
                'id' => $item->id,
                'balance' => $balance,
                'point' => $point
            ];
        }

        // 일괄 업데이트
        if(count($updates)) {
            DB::table('user_emoney_log')
                ->upsert($updates, ['id'], ['balance', 'point']);
        }

        $this->popupDelete = false;
        $this->cancel();
    }


    /**
     * 삭제
     */
    public function delete($id)
    {
        if($this->forms['trans']) {
            $this->message = "출금 로그 삭제 불가, 원본 동작으로 취소해 주세요.";

        } else {
            $this->popupDelete = true;
        }
    }

    /**
     * 삭제 확인
     */
    public function deleteConfirm()
    {

        //dd($this->forms);
        // 삭제 전 조회
        $row = DB::table('user_emoney_log')
            ->where('id', '<', $this->forms['id'])
            ->orderBy('id', 'desc')
            ->first();
        //dd($row);
        if($row) {
            $id = $row->id;
            $balance = $row->balance;
            $point = $row->point;
        } else {
            $id = $this->forms['id'];
            $balance = 0;
            $point = 0;
        }

        // 삭제
        DB::table('user_emoney_log')
            ->where('id', $this->forms['id'])
            ->delete();


        // 출금 로그 변경
        //dump("delete");

        // if($this->forms['trans'] == 'user_emoney_withdraw') {
        //     $withdraw = DB::table('user_emoney_withdraw')
        //         ->where('id', $this->forms['trans_id'])
        //         ->first();
        //     if($withdraw) {
        //         if($withdraw->checked) {
        //             $checked = null;
        //         } else {
        //             $checked = 1;
        //         }

        //         $checked_at = date('Y-m-d H:i:s');

        //         DB::table('user_emoney_withdraw')
        //             ->where('id', $this->forms['trans_id'])
        //             ->update([
        //                 'status' => 'delete',
        //                 'checked' => $checked,
        //                 'checked_at' => $checked_at,
        //                 'log_id' => null
        //             ]);
        //     }

        //     //dd($withdraw);
        // }

        // =====


        // 금액 다시 계산
        $rows = DB::table('user_emoney_log')
            ->where('user_id', $this->forms['user_id'])
            ->where('id', '>', $id)
            ->orderBy('created_at', 'asc')
            ->get();


        $updates = [];
        foreach($rows as $item) {
            if(isset($item->deposit)) {
                $balance += $item->deposit;

                if(isset($item->type) && $item->type == 'point') {
                    $point += $item->deposit;
                }
            }

            if(isset($item->withdraw)) {
                $balance -= $item->withdraw;

                if(isset($item->type) && $item->type == 'point') {
                    $point -= $item->withdraw;
                }
            }

            $updates[] = [
                'id' => $item->id,
                'balance' => $balance,
                'point' => $point
            ];
        }

        // 일괄 업데이트
        if(count($updates)) {
            DB::table('user_emoney_log')
                ->upsert($updates, ['id'], ['balance', 'point']);
        }





        $this->popupDelete = false;
        $this->cancel();
    }

    public function search()
    {
        $this->resetPage();
    }

    public function searchReset()
    {
        $this->search_keyword = '';
        $this->resetPage();
    }



}
