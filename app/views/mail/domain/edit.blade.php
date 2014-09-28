@extends ('layout.master')

@section ('pageTitle')
E-maildomein bewerken
@endsection

@section ('content')
<form action="/mail/domain/{{ $domain->id }}/edit" method="POST" data-abide>
	<fieldset>
		<legend>E-maildomein bewerken</legend>
		<div>
			<label>Domein:
				<input type="text" name="domain" value="{{ Input::old ('domain', $domain->domain) }}" required />
			</label>
			<small class="error">Verplicht veld</small>
		</div>
		<div>
			{{ Form::token () }}
			<button name="save" value="{{ $domain->id }}">Opslaan</button>
		</div>
	</fieldset>
</form>
@endsection