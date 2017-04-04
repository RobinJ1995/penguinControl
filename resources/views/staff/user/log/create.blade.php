@extends ('layout.master')

@section ('pageTitle')
Facturatie: toevoegen &bull; Staff
@endsection

@section ('content')
<form action="/staff/user/log/create" method="POST" data-abide>
	<fieldset>
		<legend>Gebruikerlog toevoegen</legend>
		<div class="row">
			<div class="large-12 medium-12 small-12 column">
				<label>Gebruiker:
					<!--<input type="number" name="user_info_id" value="{{ Input::old ('user_info_id') }}" required />-->
					
					{{ Form::select
				(
					'user_info_id',
					$users,
					Input::old ('user_info_id')
				)
				}}
				</label>
				<small class="error">Invalid input</small>
			</div>
		</div>
		<div class="row">
			<div class="large-4 medium-4 small-12 column">
				<label>Datum/tijd:
					<input type="text" name="time" value="{{ Input::old ('time') }}" required />
				</label>
				<small class="error">Invalid input</small>
			</div>
			<div class="large-4 medium-4 small-12 column">
				<label>Nieuw:
					{{ Form::select
						(
							'nieuw',
							array
							(
								'0' => 'Nee',
								'1' => 'Ja',
							),
							Input::old ('nieuw', 0)
						)
					}}
				</label>
				<small class="error">Invalid input</small>
			</div>
			<div class="large-4 medium-4 small-12 column">
				<label>Gefactureerd:
					{{ Form::select
						(
							'boekhouding',
							$boekhoudingBetekenis,
							Input::old ('boekhouding', 0)
						)
					}}
				</label>
				<small class="error">Invalid input</small>
			</div>
		</div>
		<div>
			{{ Form::token () }}
			<button name="save" value="{{ time () }}">Opslaan</button>
		</div>
	</fieldset>
</form>
@endsection