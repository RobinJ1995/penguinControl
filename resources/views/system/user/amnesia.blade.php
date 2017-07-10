@extends ('layout.master')

@section ('pageTitle')
Amnesia
@endsection

@section ('content')
<div class="large-3 medium-3 hide-for-small-down column">
        <br />
</div>
<div class="large-6 medium-6 small-12 column">
	<p>Please enter either your username or e-mail address. You will then receive an e-mail containing further instructions.</p>
	
        <form method="POST" data-abide>
                <div>
                        <label>Username/e-mail address:
                                <input type="text" name="something" value="{{ Input::old ('something') }}" required />
                        </label>
                        <small class="error">Enter your username or e-mail address.</small>
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
