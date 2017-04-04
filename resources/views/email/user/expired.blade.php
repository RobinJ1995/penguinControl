@extends ('email.template')

@section ('content')
<p>Dear  {{ $userInfo->getFullName () }}</p>

<p>We have just received a request to renew your account.<br />
To confirm the renewal of your account, please following this link: <a href="{{ $url }}">{{ $url }}</a></p>
@endsection