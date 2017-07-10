@extends ('layout.master')

@section ('pageTitle')
Edit FTP account
@endsection

@section ('alerts')
@parent
{!! new Alert ('Leave the password fields empty if you do not wish to change your current password.', Alert::TYPE_INFO) !!}
@endsection

@section ('content')
<form action="/ftp/{{ $ftp->id }}/edit" method="POST" data-abide>
	<fieldset>
		<legend>Edit FTP account</legend>
		<div>
			<label>Username:
				@if (is_admin () && $ftp->user->userInfo->username == $ftp->username)
					<input type="text" name="username" value="{{ Input::old ('username', $ftp->username) }}" disabled />
				@else
					<div class="row collapse">
						<div class="large-4 medium-6 small-12 column">
							<span class="prefix">{{ $ftp->user->userInfo->username }}_</span>
						</div>
						<div class="large-8 medium-6 small-12 column">
							<input type="text" name="username" value="{{ Input::old ('username', substr ($ftp->username, strlen ($ftp->user->userInfo->username) + 1)) }}" />
						</div>
					</div>
				@endif
			</label>
		</div>
		<div>
			<label>Password:
				<input type="password" name="passwd" id="newPass" value="" />
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
						<span class="prefix">{{ $ftp->user->homedir }}/</span>
					</div>
					<div class="large-8 medium-6 small-12 column">
						<input type="text" name="dir" value="{{ Input::old ('dir', substr ($ftp->dir, strlen ($ftp->user->homedir) + 1)) }}" />
					</div>
				</div>
			</label>
			<small class="error">Invalid input</small>
		</div>
		<div>
			{{ Form::token () }}
			<button name="save" value="{{ time () }}">Save</button>
		</div>
	</fieldset>
</form>
@endsection