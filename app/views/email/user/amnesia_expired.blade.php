@extends ('email.template')

@section ('content')
<p>Beste {{ $userInfo->getFullName () }}</p>

<p>Er is zojuist een aanvraag ingediend om uw inloggegevens door te geven en/of te wijzigen. Indien u deze aanvraag niet heeft ingediend kunt u deze e-mail best negeren. Indien u e-mails zoals deze regelmatig ontvangt zonder deze zelf aangevraagd te hebben, dan kan het zijn dat iemand misbruik probeert te maken van uw SIN-account. <a href="https://sinners.be/page/contact">Contacteer ons</a> zeker in dit geval!</p>
<p>Wij hebben uw wachtwoord tijdelijk ingesteld op <kbd>' . $random </kbd>. U kunt dit wachtwoord gebruiken in combinatie met uw gebruikersnaam (<kbd>' . $userInfo->username </kbd>) om in te loggen op onze website en uw account (die vervallen is) te verlengen.<br />
Wanneer uw account verlengt is dient u zelf een nieuw wachtwoord in te stellen via <em>Gebruiker -> Gegevens wijzigen</em>. Zolang u zelf geen nieuw wachtwoord heeft ingesteld zult u geen gebruik kunnen maken van sommige andere diensten zoals FTP.</p>
<p>Wij horen het graag indien u verdere vragen of problemen heeft.</p>
@endsection