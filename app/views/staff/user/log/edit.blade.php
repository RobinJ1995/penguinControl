@extends ('layout.master')

@section ('pageTitle')
Facturatie bewerken &bull; Staff
@endsection

@section ('content')
<form action="/staff/user/log/{{ $userlog->id }}/edit" method="POST" data-abide>
	<fieldset>
		<legend>Gebruikerlog bewerken</legend>
		<div class="row">
			<div class="large-4 medium-4 small-12 column">
				<label>Gefactureerd:
					{{ Form::select
						(
							'boekhouding',
							array
							(
								'-1'=>'Niet te factureren',
								'0'=>'Nog te factureren',
								'1'=>'Gefactureerd'
							),
							$userlog->boekhouding
						)
					}}
				</label>
				<small class="error">Ongeldige waarde</small>
			</div>
		</div>
		<div>
			{{ Form::token () }}
			<button name="save" value="{{ $userlog->id }}">Opslaan</button>
		</div>
	</fieldset>
</form>
@endsection
