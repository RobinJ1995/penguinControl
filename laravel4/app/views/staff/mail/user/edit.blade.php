@extends ('layout.master')

@section ('pageTitle')
E-mailaccount bewerken &bull; Staff
@endsection

@section ('content')
<form action="/staff/mail/user/{{ $mUser->id }}/edit" method="POST" data-abide>
	<fieldset>
		<legend>E-mailaccount bewerken</legend>
		<div class="row">
			<div class="large-7 medium-6 small-12 column">
				<label>E-mailadres:
					<input type="text" name="email" value="{{ Input::old ('email', $mUser->email) }}" required />
				</label>
				<small class="error">Verplicht veld</small>
			</div>
			<div class="large-5 medium-6 small-12 column">
				<label>Domein:
					{{ Form::select
						(
							'domain',
							$domains,
							Input::old ('domain', $mUser->mail_domain_virtual_id)
						)
					}}
				</label>
				<small class="error">Verplicht veld</small>
			</div>
		</div>
		<div class="row">
			<div class="large-6 medium-6 small-12 column">
				<label>Wachtwoord:
					<input type="password" name="password" id="newPass" value="" />
				</label>
				<small class="error">Verplicht veld</small>
			</div>
			<div class="large-6 medium-6 small-12 column">
				<label>Wachtwoord (bevestiging):
					<input type="password" name="password_confirm" value="" data-equalto="newPass" />
				</label>
				<small class="error">Bevestig uw nieuwe wachtwoord door het een tweede keer in te geven.</small>
			</div>
		</div>
		<div>
			{{ Form::token () }}
			<button name="save" value="{{ $mUser->id }}">Opslaan</button>
		</div>
	</fieldset>
</form>
@endsection
