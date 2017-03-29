@extends ('email.template')

@section ('content')
<p>Beste {{ $userInfo->getFullName () }}</p>
<p>Er is zojuist een aanvraag ingediend om uw inloggegevens door te geven en/of te wijzigen. Indien u deze aanvraag niet heeft ingediend kunt u deze e-mail best negeren. Indien u e-mails zoals deze regelmatig ontvangt zonder deze zelf aangevraagd te hebben, dan kan het zijn dat iemand misbruik probeert te maken van uw SIN-account. <a href="https://sinners.be/page/contact">Contacteer ons</a> zeker in dit geval!</p>
<p>U kunt de volgende link eenmalig gebruiken om in te loggen op uw SIN-account: <a href="{{ $url }}">{{ $url }}</a><br />
U kunt vervolgens indien gewenst uw wachtwoord wijzigen via <em>Gebruiker -> Gegevens wijzigen</em>.</p>
<p>Wij horen het graag indien u verdere vragen of problemen heeft.</p>
@endsection