@extends ('layout.master')

@section ('pageTitle')
	Create FTP account
@endsection

@section ('content')
<form action="/ftp/create" method="POST" data-abide>
	<fieldset>
		<legend>Create FTP account</legend>
		<div>
			<label>Username:
				<div class="row collapse">
					<div class="large-4 medium-6 small-12 column">
						<span class="prefix">{{ $userInfo->username }}_</span>
					</div>
					<div class="large-8 medium-6 small-12 column">
						<input type="text" name="username" value="{{ Input::old ('username') }}" />
					</div>
				</div>
			</label>
			<small class="error">Required field</small>
		</div>
		<div>
			<label>Password:
				<input type="password" name="passwd" id="newPass" value="" required />
			</label>
			<small class="error">Required field</small>
		</div>
		<div>
			<label>Password (confirmation):
				<input type="password" name="passwd_confirm" value="" data-equalto="newPass" />
			</label>
			<small class="error">Confirm your password by entering it a second time.</small>
		</div>
		<div>
			<label>Directory:
				<div class="row collapse">
					<div class="large-4 medium-6 small-12 column">
						<span class="prefix">{{ $user->homedir }}/</span>
					</div>
					<div class="large-8 medium-6 small-12 column">
						<input type="text" name="dir" value="{{ Input::old ('dir') }}" />
					</div>
				</div>
			</label>
			<small class="error">Invalid input</small>
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