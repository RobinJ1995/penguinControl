@extends ('layout.master')

@section ('pageTitle')
Account verlengen
@endsection

@section ('content')
<div class="large-3 medium-3 hide-for-small-down column">
        <br />
</div>
<div class="large-6 medium-6 small-12 column">
	<p>Geef opnieuw uw gebruikersnaam en wachtwoord in om uw account te verlengen. Er zal vervolgens een e-mail gestuurd worden naar uw opgegeven e-mailadres met verdere instructies.</p>
	<p>Een SIN-account kost 5 euro per jaar, dus bij een verlenging zal dit bedrag in rekening gebracht worden (behalve voor docenten).</p>
	
        <form method="POST" data-abide>
                <div>
                        <label>Gebruikersnaam:
                                <input type="text" name="username" value="{{ Input::old ('username') }}" required />
                        </label>
                        <small class="error">Geef uw gebruikersnaam in.</small>
                </div>
                <div>
                        <label>Wachtwoord:
                                <input type="password" name="password" required />
                        </label>
                        <small class="error">Geef uw wachtwoord in.</small>
                </div>
		<div>
			<input type="checkbox" name="renew" value="yes" /> Ik wens mijn account te verlengen tot {{ isset ($nextYear) ? '1 oktober 20' . $nextYear : '1 oktober volgend jaar' }}
                </div>
                <div>
                        {{ Form::token () }}
                        <button name="time" value="{{ time () }}">Bevestigen</button>
                </div>
        </form>

	<p class="alert-box warning">Gebruik dit formulier <strong>niet</strong> wanneer het om een account van een organisatie gaat! Organisaties moeten <a href="/page/contact">contact met ons opnemen</a> om hun account te laten verlengen!</p>
	<p class="alert-box info">Er lijkt in het verleden iets misgegaan te zijn bij de registratie van bepaalde gebruikers. Het kan hierdoor zijn dat u verteld wordt ons te contacteren wanneer u uw account probeert te verlengen. Dit is zodat wij kunnen nakijken wat er net mis is en deze fout kunnen corrigeren. Wij bieden onze excuses aan voor het eventuele ongemak.</p>
</div>
<div class="large-3 medium-3 hide-for-small-down column">
        <br />
</div>
@endsection
