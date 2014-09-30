@extends ('layout.master')

@section ('pageTitle')
Inloggegevens vergeten
@endsection

@section ('content')
<div class="large-3 medium-3 hide-for-small-down column">
        <br />
</div>
<div class="large-6 medium-6 small-12 column">
	<p>Indien u uw inloggegevens niet meer weet kunt u hier uw gebruikersnaam, e-mailadres of studentennummer ingeven. Vervolgens zal er een e-mail gestuurd worden naar het door u ingestelde e-mailadres met verdere instructies.</p>
	
        <form method="POST" data-abide>
                <div>
                        <label>Gebruikersnaam/e-mailadres/r-nummer:
                                <input type="text" name="something" value="{{ Input::old ('something') }}" required />
                        </label>
                        <small class="error">Geef uw gebruikersnaam, e-mailadres of r-nummer in.</small>
                </div>
                <div>
                        {{ Form::token () }}
                        <button name="time" value="{{ time () }}">Bevestigen</button>
                </div>
        </form>
</div>
<div class="large-3 medium-3 hide-for-small-down column">
        <br />
</div>
@endsection
