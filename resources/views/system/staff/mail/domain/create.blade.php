@extends ('layout.master')

@section ('pageTitle')
Add e-mail domain &bull; Staff
@endsection

@section ('content')
<form action="/staff/mail/domain/create" method="POST" data-abide>
	<fieldset>
		<legend>Add e-mail domain</legend>
		<div>
			<label>Owner:
				{{ Form::select
				(
					'uid',
					$users,
					old ('uid', $user->uid)
				)
				}}
			</label>
		</div>
		<div>
			<label>Domain:
				<input type="text" name="domain" value="" required />
			</label>
			<small class="error">Required field</small>
		</div>
		<div>
			{{ Form::token () }}
			<button name="save" value="{{ time () }}">Save</button>
		</div>
	</fieldset>
</form>
@endsection