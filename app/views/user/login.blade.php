@extends ('layout.master')

@section ('pageTitle')
Login
@endsection

@section ('content')
<div class="large-4 medium-3 hide-for-small-down column">
	<br />
</div>
<div class="large-4 medium-6 small-12 column">
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
			{{ Form::token () }}
			<button name="time" value="{{ time () }}">Login</button>
		</div>
	</form>
	<p>
		<!--<a href="/p/user/forgot">Wachtwoord vergeten?</a>-->
	</p>
</div>
<div class="large-4 medium-3 hide-for-small-down column">
        <br />
</div>
@endsection
