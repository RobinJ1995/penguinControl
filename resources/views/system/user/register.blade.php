@extends ('layout.master')

@section ('pageTitle')
Sign up
@endsection

@section ('js')
@parent
<script src="/js/register.js"></script>
@endsection

@section ('content')
<form action="/user/register" method="POST" data-abide>
	<fieldset>
		<legend>Sign up</legend>
		<fieldset>
			<legend>Credentials</legend>
			<div class="row">
				<div class="large-4 medium-6 small-12 column">
					<label>Username:
						<input type="text" name="username" value="{{ Input::old ('username') }}" required />
					</label>
					<small class="error">Invalid input</small>
				</div>
				<div class="large-8 medium-6 small-12 column">
					<p>You will use this username to log on to the services we provide. This can only be lower case.</p>
				</div>
			</div>
			<div class="row">
				<div class="large-4 medium-6 small-12 column">
					<div>
						<label>Password:
							<input type="password" name="password" id="newPass" required />
						</label>
						<small class="error">Invalid input</small>
					</div>
					<div>
						<label>Password (confirmation):
							<input type="password" name="password_confirm" data-equalto="newPass" required />
						</label>
						<small class="error">Invalid input</small>
					</div>
				</div>
				<div class="large-8 medium-6 small-12 column">
					<p>This is the password you will use in combination with your username, to log on to the services we provide. Please choose a strong password that can not easily be guessed by other people</p>
					<p>We ask you to enter your password twice to prevent typos.</p>
				</div>
			</div>
		</fieldset>
		<fieldset>
			<legend>Contact information</legend>
			<div class="row">
				<div class="large-6 medium-6 small-12 column">
					<label>First name:
						<input type="text" name="fname" value="{{ Input::old ('fname') }}" required />
					</label>
					<small class="error">Invalid input</small>
				</div>
				<div class="large-6 medium-6 small-12 column">
					<label>Surname:
						<input type="text" name="lname" value="{{ Input::old ('lname') }}" required />
					</label>
					<small class="error">Invalid input</small>
				</div>
			</div>
			<div class="row">
				<div class="large-4 medium-6 small-12 column">
					<label>E-mail address:
						<input type="email" name="email" value="{{ Input::old ('email') }}" required />
					</label>
					<small class="error">Invalid input</small>
				</div>
				<div class="large-8 medium-6 small-12 column">
					<p>We will use this e-mail address for any correspondence relating to our services and your usage of them. Please keep this e-mail address up-to-date, as you will need it in case you forget your password.</p>
				</div>
			</div>
		</fieldset>
		<fieldset>
			<legend>Terms and conditions</legend>
			<input type="checkbox" name="termsAgree" value="yes" /> I agree to <a href="/etc/algemene_gebruiksvoorwaarden.pdf">the terms and conditions</a>.
		</fieldset>
		<div>
			{{ Form::token () }}
			<button name="save" value="{{ time () }}">Sign up</button>
		</div>
	</fieldset>
</form>
@endsection
