<?php
namespace Jiny\Auth\App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

/**
 * 사용자 메시지
 */
class HomeUserMessage extends Component
{
    public $user_id;
    public $viewFile;

    public function mount()
    {
        if(!$this->viewFile) {
            $this->viewFile = 'jiny-users::home.message.list';
        }

        if(!$this->user_id) {
            $this->user_id = Auth::user()->id;
        }

    }

    public function render()
    {

        $message = DB::table('user_messages')
            ->where(function($query) {
                $query->where('user_id', $this->user_id)
                    ->orWhere('notice', 1);
            })
            ->where('enable', 1)
            ->paginate(10);

        return view($this->viewFile, [
            'message' => $message
        ]);
    }

}
