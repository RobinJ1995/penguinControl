@extends ('layout.master')

@section ('pageTitle')
Doorstuuradres bewerken &bull; Staff
@endsection

@section ('content')
<form action="/staff/mail/forwarding/{{ $mFwd->id }}/edit" method="POST" data-abide>
	<fieldset>
		<legend>Doorstuurdadres bewerken</legend>
		<div class="row">
			<div class="large-7 medium-6 small-12 column">
				<label>E-mailadres:
					<input type="text" name="source" value="{{ Input::old ('source', $mFwd->source) }}" required />
				</label>
				<small class="error">Required field</small>
			</div>
			<div class="large-5 medium-6 small-12 column">
				<label>Domein:
					{{ Form::select
						(
							'domain',
							$domains,
							Input::old ('domain', $mFwd->mail_domain_virtual_id)
						)
					}}
				</label>
				<small class="error">Required field</small>
			</div>
		</div>
		<div class="row">
			<div class="large-12 medium-12 small-12 column">
				<label>Bestemming:
					<input type="email" name="destination" value="{{ Input::old ('destination', $mFwd->destination) }}" required />
				</label>
				<small class="error">Geef een geldig e-mailadres in</small>
			</div>
		</div>
		<div>
			{{ Form::token () }}
			<button name="save" value="{{ $mFwd->id }}">Opslaan</button>
		</div>
	</fieldset>
</form>
@endsection
