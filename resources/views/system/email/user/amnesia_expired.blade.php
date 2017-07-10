@extends ('email.template')

@section ('content')
<p>Dear {{ $userInfo->getFullName () }}</p>

<p>We have just received a request to help you regain access to your account. If you did not send this request, then please ignore this e-mail.</p>
<p>Your password has been temporarily reset to <kbd>{{ $random }}</kbd>. You may use thus password in combination with your username (<kbd>{{ $userInfo->username }}</kbd>) to login to your account (which has expired) and renew it.<br />
Once your account has been renewed, you should change your password by going to <em>User -> Modify account</em>. You may not be able to access some of the services tied to your account (like FTP) until you have done so.</p>
@endsection