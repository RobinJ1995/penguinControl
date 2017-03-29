@extends ('layout.master')

@section ('pageTitle')
FTP-account bewerken
@endsection

@section ('content')
<form action="/ftp/{{ $ftp->id }}/edit" method="POST" data-abide>
	<fieldset>
		<legend>FTP-account bewerken</legend>
		<div>
			<label>Gebruikersnaam:
				<div class="row collapse">
					<div class="large-4 medium-6 small-12 column">
						<span class="prefix">{{ $userInfo->username }}_</span>
					</div>
					<div class="large-8 medium-6 small-12 column">
						<input type="text" name="user" value="{{ Input::old ('user', substr ($ftp->user, strlen ($userInfo->username) + 1)) }}" required />
					</div>
				</div>
			</label>
			<small class="error">Verplicht veld</small>
		</div>
		<div>
			<label>Wachtwoord:
				<input type="password" name="passwd" id="newPass" value="" />
			</label>
			<small class="error">Verplicht veld</small>
		</div>
		<div>
			<label>Wachtwoord (bevestiging):
				<input type="password" name="passwd_confirm" value="" data-equalto="newPass" />
			</label>
			<small class="error">Bevestig uw nieuwe wachtwoord door het een tweede keer in te geven.</small>
		</div>
		<div>
			<label>Map:
				<div class="row collapse">
					<div class="large-4 medium-6 small-12 column">
						<span class="prefix">{{ $user->homedir }}/</span>
					</div>
					<div class="large-8 medium-6 small-12 column">
						<input type="text" name="dir" value="{{ Input::old ('dir', substr ($ftp->dir, strlen ($user->homedir) + 1)) }}" />
					</div>
				</div>
			</label>
			<small class="error">Ongeldige waarde</small>
		</div>
		<div>
			{{ Form::token () }}
			<button name="save" value="{{ time () }}">Opslaan</button>
		</div>
	</fieldset>
</form>
@endsection