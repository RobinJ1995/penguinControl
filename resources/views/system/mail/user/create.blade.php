@extends ('layout.master')

@section ('pageTitle')
Create e-mail account
@endsection

@section ('content')
<form action="/mail/user/create" method="POST" data-abide>
	<fieldset>
		<legend>Create e-mail account</legend>
		<div class="row">
			<div class="large-7 medium-6 small-12 column">
				<label>E-mail address:
					<input type="text" name="email" value="{{ Input::old ('email') }}" required />
				</label>
				<small class="error">Required field</small>
			</div>
			<div class="large-5 medium-6 small-12 column">
				<label>Domain:
					{{ Form::select
						(
							'domain',
							$domains,
							Input::old ('domain')
						)
					}}
				</label>
				<small class="error">Required field</small>
			</div>
		</div>
		<div class="row">
			<div class="large-6 medium-6 small-12 column">
				<label>Password:
					<input type="password" name="password" id="newPass" value="" required />
				</label>
				<small class="error">Required field</small>
			</div>
			<div class="large-6 medium-6 small-12 column">
				<label>Password (confirmation):
					<input type="password" name="password_confirm" value="" data-equalto="newPass" />
				</label>
				<small class="error">Please confirm your password by entering it a second time.</small>
			</div>
		</div>
		@section ('custom_fields')
		@show
		<div>
			{{ Form::token () }}
			<button name="save" value="{{ time () }}">Save</button>
		</div>
	</fieldset>
</form>
@endsection