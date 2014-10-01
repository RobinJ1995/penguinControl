@extends ('layout.master')

@section ('pageTitle')
Systeemopdracht toevoegen &bull; Staff
@endsection

@section ('content')
<form action="/staff/systemtask/create" method="POST" data-abide>
	<fieldset>
		<legend>Systeemopdracht toevoegen</legend>
		<div class="row">
			<div class="large-12 medium-12 small-12 column">
				<label>Type:
					{{ Form::select
					(
						'type',
						array
						(
							'apache_reload' => 'Webserver opnieuw laden',
							'custom' => 'Commando'
						),
						Input::old ('type')
					)
					}}
				</label>
				<small class="error">Ongeldige waarde</small>
			</div>
		</div>
		<div class="row">
			<div class="large-12 medium-12 small-12 column">
				<label>Commando:
					<input type="text" name="command" value="{{ Input::old ('command') }}" />
				</label>
				<small class="error">Ongeldige waarde</small>
			</div>
		</div>
		<div class="row">
			<div class="large-6 medium-6 small-12 column">
				<label>Start:
					<input type="text" name="start" value="{{ Input::old ('start') }}" placeholder="DD-MM-YYYY HH:MM:SS" />
				</label>
				<small class="error">Ongeldige waarde</small>
			</div>
			<div class="large-6 medium-6 small-12 column">
				<label>Einde:
					<input type="text" name="end" value="{{ Input::old ('end') }}" placeholder="DD-MM-YYYY HH:MM:SS" />
				</label>
				<small class="error">Ongeldige waarde</small>
			</div>
		</div>
		<div class="row">
			<div class="large-6 medium-6 small-12 column">
				<label>Interval:
					<input type="number" name="interval" value="{{ Input::old ('interval') }}" />
				</label>
				<small class="error">Ongeldige waarde</small>
			</div>
			<div class="large-6 medium-6 small-12 column">
				<label>&nbsp;
					{{ Form::select
					(
						'interval_unit',
						array
						(
							'sec' => 'seconden',
							'min' => 'minuten',
							'hour' => 'uur',
							'day' => 'dagen',
							'week' => 'weken'
						),
						Input::old ('interval_unit')
					)
					}}
				</label>
				<small class="error">Ongeldige waarde</small>
			</div>
		</div>
		<div>
			{{ Form::token () }}
			<button name="save" value="{{ time () }}">Opslaan</button>
		</div>
	</fieldset>
</form>
@endsection