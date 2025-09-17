<?php
namespace Jiny\Auth\App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


class ProfileAccount extends Component
{
    public $form;
    public $user_id;
    public $row_id;

    public function mount()
    {
        $user = Auth::user();
        $this->user_id = $user->id;

        if($this->user_id) {
            $row = DB::table('accounts')
            ->where('user_id',$this->user_id)
            ->first();

            if($row) {
                $this->row_id = $row->id;
                $this->form = get_object_vars($row);
            } else {
                $this->form['user_id'] = $user->id;
            }
        } else {
            $this->form['user_id'] = $user->id;
        }

    }

    public function render()
    {

        $viewFile = 'jiny-profile::livewire.account';
        return view($viewFile);
    }

    public function submit()
    {
        $form = $this->form;

        if($this->row_id) {
            DB::table('accounts')
                ->where('id',$this->user_id)
                ->update($form);
        } else {
            $form['user_id'] = $this->user_id;
            DB::table('accounts')
                ->insert($form);
        }
    }

    public function cancel()
    {
        $this->form = [];
    }

}
