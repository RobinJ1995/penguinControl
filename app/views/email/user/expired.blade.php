@extends ('email.template')

@section ('content')
<p>Beste  {{ $userInfo->getFullName () }}</p>

<p>Er is zojuist een verlenging aangevraagd voor uw SIN-account.<br />
Om deze verlenging te bevestigen, open de volgende link in uw webbrowser: <a href="{{ $url }}">{{ $url }}</a></p>
@endsection