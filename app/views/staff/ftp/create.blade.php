@extends ('layout.master')

@section ('pageTitle')
FTP-account toevoegen &bull; Staff
@endsection

@section ('content')
<form action="/staff/ftp/create" method="POST" data-abide>
	<fieldset>
		<legend>FTP-account toevoegen</legend>
		<div>
			<label>Eigenaar:
				{{ Form::select
				(
					'uid',
					$users,
					Input::old ('uid', $user->uid)
				)
				}}
			</label>
		</div>
		<div>
			<label>Gebruikersnaam:
				<div class="row collapse">
					<div class="large-4 medium-6 small-12 column">
						<span class="prefix"><em>&lt;username&gt;</em>_</span>
					</div>
					<div class="large-8 medium-6 small-12 column">
						<input type="text" name="user" value="{{ Input::old ('user') }}" />
					</div>
				</div>
			</label>
			<small class="error">Verplicht veld</small>
		</div>
		<div>
			<label>Wachtwoord:
				<input type="password" name="passwd" id="newPass" value="" required />
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
				<input type="text" name="dir" value="{{ Input::old ('dir') }}" />
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