{{-- 사용자 아바타 이미지 출력 --}}
@if($user_id)
<img src="/home/user/avatar/{{$user_id}}"
    width="30"
    {{$attributes->merge(['class' => 'rounded-circle'])}}/>
@else
<span class="rounded-circle bg-gray-300"
style="width:30px; height:30px;">

</span>
@endif

