<?php
namespace Jiny\Auth\App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

/**
 * 사용자 리뷰
 */
class HomeUserReviews extends Component
{
    public $user_id;
    public $viewFile;

    public function mount()
    {
        if(!$this->user_id) {
            $this->user_id = Auth::id();
        }

        if(!$this->viewFile) {
            $this->viewFile = 'jiny-users::home.reviews.list';
        }
    }

    public function render()
    {
        $reviews = DB::table('user_reviews')
            ->where('user_id', Auth::id())
            ->paginate(10);

        return view($this->viewFile, [
            'reviews' => $reviews
        ]);
    }

}
