@extends ('layout.master')

@section ('pageTitle')
Edit e-mail domain &bull; Staff
@endsection

@section ('content')
<form action="/staff/mail/domain/{{ $domain->id }}/edit" method="POST" data-abide>
	<fieldset>
		<legend>Edit e-mail domain</legend>
		<div>
			<label>Owner:
				{{ Form::select
				(
					'uid',
					$users,
					old ('uid', $domain->uid)
				)
				}}
			</label>
		</div>
		<div>
			<label>Domain:
				<input type="text" name="domain" value="{{ old ('domain', $domain->domain) }}" required />
			</label>
			<small class="error">Required field</small>
		</div>
		<div>
			{{ Form::token () }}
			<button name="save" value="{{ $domain->id }}">Save</button>
		</div>
	</fieldset>
</form>
@endsection