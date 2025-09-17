<?php
namespace Jiny\Auth\App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileStatus extends Component
{
    public $viewFile;
    public $user = [];
    public $userType;
    public $lastLog;

    public function mount()
    {
        if(!$this->viewFile) {
            $this->viewFile = "jiny-profile::livewire.status";
        }

        $user = Auth::user();
        $this->user['name'] = $user->name;
        $this->user['email'] = $user->email;

        // 유저 타입 및 권한
        $this->userType = $user->utype;

        $this->lastLog = userLastLog();

    }

    public function render()
    {
        return view($this->viewFile);
    }



}
