@extends ('layout.master')

@section ('pageTitle')
Gegevens wijzigen
@endsection

@section ('content')
<form method="POST" data-abide>
	<div class="row">
		<div class="large-6 medium-6 small-12 column">
			<label>Shell:
				{{ Form::select
					(
						'shell',
						array
						(
							'/bin/bash' => 'Bash',
							'/usr/bin/fish' => 'Fish',
							'/usr/bin/zsh' => 'ZSH',
							'/usr/bin/tmux' => 'Tmux'
						),
						Input::old ('shell', $user->shell)
					)
				}}
			</label>
			<small class="error">Ongeldige waarde</small>
		</div>
		<div class="large-6 medium-6 small-12 column">
			<label>E-mailadres:
				<input type="email" name="email" required value="{{ Input::old ('email', $user->userInfo->email) }}" />
			</label>
			<small class="error">Ongeldige waarde</small>
		</div>
	</div>
	<div class="row">
		<div class="large-4 medium-12 small-12 column">
			<label>Huidige wachtwoord:
				<input type="password" name="currentPass" {{ Session::get ('isLoggedInWithToken') === true ? 'disabled' : 'required' }} />
			</label>
			<small class="error">Geef uw huidige wachtwoord in.</small>
		</div>
		<div class="large-4 medium-6 small-12 column">
			<label>Nieuwe wachtwoord:
				<input type="password" id="newPass" name="newPass" />
			</label>
			<small class="error">Geef uw nieuwe wachtwoord in.</small>
		</div>
		<div class="large-4 medium-6 small-12 column">
			<label>Nieuwe wachtwoord (bevestiging):
				<input type="password" name="newPassConfirm" data-equalto="newPass" />
			</label>
			<small class="error">Bevestig uw nieuwe wachtwoord door het een tweede keer in te geven.</small>
		</div>
	</div>
	<div>
		{{ Form::token () }}
		<button name="time" value="{{ time () }}">Gegevens wijzigen</button>
	</div>
</form>
@endsection
