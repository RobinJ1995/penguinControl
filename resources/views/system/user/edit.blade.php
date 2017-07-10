@extends ('layout.master')

@section ('pageTitle')
Modify account
@endsection

@section ('alerts')
@parent
{!! new Alert ('Leave the new password field empty if you do not wish to change your current password.', Alert::TYPE_INFO) !!}
@endsection

@section ('content')
<form method="POST" data-abide>
	<div class="row">
		<div class="large-5 medium-6 small-12 column">
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
			<small class="error">Invalid input</small>
		</div>
		<div class="large-7 medium-6 small-12 column">
			<label>E-mail address:
				<input type="email" name="email" required value="{{ Input::old ('email', $user->userInfo->email) }}" />
			</label>
			<small class="error">Invalid input</small>
		</div>
	</div>
	<div class="row">
		<div class="large-4 medium-12 small-12 column">
			<label>Current password:
				<input type="password" name="currentPass" {{ Session::get ('isLoggedInWithToken') === true ? 'disabled' : 'required' }} />
			</label>
			<small class="error">Enter your current password.</small>
		</div>
		<div class="large-4 medium-6 small-12 column">
			<label>New password:
				<input type="password" id="newPass" name="newPass" />
			</label>
			<small class="error">Enter a new password.</small>
		</div>
		<div class="large-4 medium-6 small-12 column">
			<label>New password (confirmation):
				<input type="password" name="newPassConfirm" data-equalto="newPass" />
			</label>
			<small class="error">Please confirm your new password by entering it a second time.</small>
		</div>
	</div>
	@section ('custom_fields')
	@show
	<div>
		{{ Form::token () }}
		<button name="time" value="{{ time () }}">Modify account</button>
	</div>
</form>
@endsection
