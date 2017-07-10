@extends ('layout.master')

@section ('pageTitle')
Account expired
@endsection

@section ('content')
<div class="large-3 medium-3 hide-for-small-down column">
        <br />
</div>
<div class="large-6 medium-6 small-12 column">
	<p>Please enter your username and password to renew your account. An e-mail will be sent to your e-mail address containing further instructions.</p>
	
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
		<div>
			<input type="checkbox" name="renew" value="yes" /> I wish to extend my account
                </div>
                <div>
                        {{ Form::token () }}
                        <button name="time" value="{{ time () }}">Confirm</button>
                </div>
        </form>
</div>
<div class="large-3 medium-3 hide-for-small-down column">
        <br />
</div>
@endsection
