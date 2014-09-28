@extends ('layout.master')

@section ('pageTitle')
Registreren
@endsection

@section ('js')
@parent
<script src="/js/register.js"></script>
@endsection

@section ('content')
<form action="/user/register" method="POST" data-abide>
	<fieldset>
		<legend>Registreren</legend>
		<fieldset>
			<legend>Aanmeldgegevens</legend>
			<div class="row">
				<div class="large-4 medium-6 small-12 column">
					<label>Gebruikersnaam:
						<input type="text" name="username" value="{{ Input::old ('username') }}" required />
					</label>
					<small class="error">Ongeldige waarde</small>
				</div>
				<div class="large-8 medium-6 small-12 column">
					<p>Uw gebruikersnaam is wat u zal gebruiken om aan te melden op SIN en de services die wij aanbieden. Deze moet uniek zijn en kan enkel uit letters en cijfers bestaan. Hoofdletters zullen worden vervangen door kleine letters.</p>
				</div>
			</div>
			<div class="row">
				<div class="large-4 medium-6 small-12 column">
					<div>
						<label>Wachtwoord:
							<input type="password" name="password" id="newPass" required />
						</label>
						<small class="error">Ongeldige waarde</small>
					</div>
					<div>
						<label>Wachtwoord (bevestiging):
							<input type="password" name="password_confirm" data-equalto="newPass" required />
						</label>
						<small class="error">Ongeldige waarde</small>
					</div>
				</div>
				<div class="large-8 medium-6 small-12 column">
					<p>Dit is het wachtwoord dat u zal gebruiken om aan te melden op SIN en de services die wij aanbieden. Zorg er a.u.b. voor dat dit een sterk wachtwoord is dat niet eenvoudig door andere mensen geraden kan worden.</p>
					<p>Wij vragen u om uw wachtwoord twee keer in te geven om typfouten te voorkomen.</p>
				</div>
			</div>
		</fieldset>
		<fieldset>
			<legend>Identificatie</legend>
			<div class="row">
				<div class="large-6 medium-6 small-12 column">
					<label>Voornaam:
						<input type="text" name="fname" value="{{ Input::old ('fname') }}" required />
					</label>
					<small class="error">Ongeldige waarde</small>
				</div>
				<div class="large-6 medium-6 small-12 column">
					<label>Achternaam:
						<input type="text" name="lname" value="{{ Input::old ('lname') }}" required />
					</label>
					<small class="error">Ongeldige waarde</small>
				</div>
			</div>
			<div class="row">
				<div class="large-4 medium-6 small-12 column">
					<label>r-nummer:
						<input id="veld_rnummer" type="text" name="rnummer" value="{{ Input::old ('rnummer') }}" required />
					</label>
					<small class="error">Ongeldige waarde</small>
				</div>
				<div class="large-8 medium-6 small-12 column">
					<p>Uw <em>r-nummer</em> vindt u terug op uw studentenkaart.<br />
						Sommige studenten hebben echter een <em>s-nummer</em> in plaats van een <em>r-nummer</em>. Deze studenten kunnen wel een SIN-account aanmaken, maar niet via dit formulier (deze accounts moeten namelijk manueel geverifieerd en gefactureerd worden). Indien u een <em>s-nummer</em> (of <em>u-nummer</em>) hebt en een SIN-account wenst aan te maken, gelieve dan <a href="/page/contact">contact met ons op te nemen</a>.</p>
				</div>
			</div>
			<div class="row">
				<div class="large-4 medium-6 small-12 column">
					<label>E-mailadres:
						<input id="veld_email" type="email" name="email" value="{{ Input::old ('email') }}" disabled />
					</label>
					<small class="error">Ongeldige waarde</small>
				</div>
				<div class="large-8 medium-6 small-12 column">
					<p>Dit is het e-mailadres waarop wij u zullen contacteren in het geval dat er problemen zijn met uw SIN-account. Dit e-mailadres zal <strong>niet</strong> gebruikt worden om nieuwsbrieven of andere spam naar te versturen.</p>
				</div>
			</div>
		</fieldset>
		<fieldset>
			<legend>Voorwaarden</legend>
			<input type="checkbox" name="termsAgree" value="yes" /> Ik ga akkoord met <a href="/page/gebruiksvoorwaarden">de gebruiksvoorwaarden</a>.
		</fieldset>
		<div>
			{{ Form::token () }}
			<button name="save" value="{{ time () }}">Registreren</button>
		</div>
	</fieldset>
</form>
@endsection