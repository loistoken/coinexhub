@include('email.header_new')
<h3>{{__('Hello')}}, {{ $data->first_name.' '.$data->last_name  }}</h3>
<p>
    {{__('We need to verify your email address. ')}}
</p>
<p>   {{__('Your ')}} {{allSetting()['app_title']}} {{__(' email verification code is ')}} : </p>
<h3>{{$key}}</h3>

@include('email.footer_new')
