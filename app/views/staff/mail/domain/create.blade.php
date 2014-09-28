@extends ('layout.master')

@section ('pageTitle')
E-maildomein toevoegen &bull; Staff
@endsection

@section ('content')
<form action="/staff/mail/domain/create" method="POST" data-abide>
	<fieldset>
		<legend>E-maildomein toevoegen</legend>
		<div>
			<label>Eigenaar:
				{{ Form::select
				(
					'uid',
					$users,
					Input::old ('uid', $user->uid)
				)
				}}
			</label>
		</div>
		<div>
			<label>Domein:
				<input type="text" name="domain" value="" required />
			</label>
			<small class="error">Verplicht veld</small>
		</div>
		<div>
			{{ Form::token () }}
			<button name="save" value="{{ time () }}">Opslaan</button>
		</div>
	</fieldset>
</form>
@endsection