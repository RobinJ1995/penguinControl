@extends ('layout.master')

@section ('pageTitle')
Create e-mail domain
@endsection

@section ('content')
<form action="/mail/domain/create" method="POST" data-abide>
	<fieldset>
		<legend>Create e-mail domain</legend>
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