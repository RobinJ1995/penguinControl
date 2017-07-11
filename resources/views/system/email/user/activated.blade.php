@extends ('email.template')

@section ('content')
<p>Dear {{ $userInfo->getFullName () }}</p>
<p>Your account has been acivated by an administrator. You should now be able to login.<br />
Please contact us if you experience any technical difficulties.</p>
@endsection