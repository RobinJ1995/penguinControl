@extends ('layout.master')

@section ('pageTitle')
Doorstuuradres toevoegen
@endsection

@section ('content')
<form action="/mail/forwarding/create" method="POST" data-abide>
	<fieldset>
		<legend>Doorstuuradres toevoegen</legend>
		<div class="row">
			<div class="large-7 medium-6 small-12 column">
				<label>E-mailadres:
					<input type="text" name="source" value="{{ Input::old ('source') }}" required />
				</label>
				<small class="error">Verplicht veld</small>
			</div>
			<div class="large-5 medium-6 small-12 column">
				<label>Domein:
					{{ Form::select
						(
							'domain',
							$domains,
							Input::old ('domain', '@' . $userInfo->username . '.sinners.be')
						)
					}}
				</label>
				<small class="error">Verplicht veld</small>
			</div>
		</div>
		<div class="row">
			<div class="large-12 medium-12 small-12 column">
				<label>Bestemming:
					<input type="email" name="destination" value="{{ Input::old ('destination') }}" required />
				</label>
				<small class="error">Geef een geldig e-mailadres in</small>
			</div>
		</div>
		<div>
			{{ Form::token () }}
			<button name="save" value="{{ time () }}">Opslaan</button>
		</div>
	</fieldset>
</form>
@endsection