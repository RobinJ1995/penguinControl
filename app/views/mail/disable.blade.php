@extends ('layout.master')

@section ('pageTitle')
E-mail
@endsection

@section ('content')
<form action="/mail" method="POST">
	<div class="row">
		<div class="large-9 medium-8 small-12 column">
			<p>SIN geeft gebruikers de mogelijkheid om e-mail te ontvangen en versturen via de SIN-servers. Wegens herhaaldelijk misbruik zal dit echter actief worden gecontroleerd. Bij misbruik kan uw account per direct worden geblokkeerd. Gebruikers zijn zelf verantwoordelijk voor wat er met hun account gebeurt.</p>
			<p>Wanneer u e-mail uitschakeld zullen al uw ingestelde e-maildomeinen, -addressen en doorstuuradressen worden uitgeschakeld. Deze zullen echter wel behouden blijven voor als u later beslist dat u e-mail terug wil inschakelen.</p>
		</div>
		<div class="large-3 medium-4 small-12 column">
			<button name="disable" value="{{ time () }}" style="margin-top: 3px;">E-mail uitschakelen</button>
		</div>
	</div>
</form>
@endsection