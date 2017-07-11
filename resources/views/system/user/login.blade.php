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
			<label>Username:
				<input type="text" name="username" value="{{ Input::old ('username') }}" required />
			</label>
			<small class="error">Enter your username.</small>
		</div>
		<div>
			<label>Password:
				<input type="password" name="password" required />
			</label>
			<small class="error">Enter your password.</small>
		</div>
		@section ('custom_fields')
		@show
		<div>
			{{ Form::token () }}
			<button name="time" value="{{ time () }}">Login</button>
		</div>
	</form>
	<p>
		<a href="/user/amnesia">Forgot your credentials?</a>
	</p>
</div>
<div class="large-4 medium-3 hide-for-small-down column">
        <br />
</div>
@endsection
