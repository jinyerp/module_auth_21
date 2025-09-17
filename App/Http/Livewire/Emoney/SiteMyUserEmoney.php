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
class SiteMyUserEmoney extends Component
{
    use WithPagination;
    public $user_id;

    public function render()
    {
        $emoney = DB::table("user_emoney_log")
            ->where('user_id', Auth::user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('jiny-users-emoney::home.emoney.log',[
            'emoney'=>$emoney
        ]);
    }
}
