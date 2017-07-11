@extends ('email.template')

@section ('content')
<p>Dear {{ $userInfo->getFullName () }}</p>
<p>We have just received a request to help you regain access to your account. If you did not send this request, then please ignore this e-mail.</p>
<p>The following link will allow you to login to your account (once!) without the need to enter your password: <a href="{{ $url }}">{{ $url }}</a><br />
You may then change your password by going to <em>User -> Modify account</em>.</p>
@endsection