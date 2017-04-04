@extends ('layout.master')

@section ('pageTitle')
Facturatie bewerken &bull; Staff
@endsection

@section ('content')
<form action="/staff/user/log/export" method="POST" data-abide>
	<div class="row">
		<div class="large-12 column">
			<h2>Exporteren</h2>
			<div class="row">
				<input type="hidden" name="userLogId" value='{{json_encode ($userLogsIds)}}'/>
				<div class="large-6 medium-12 column">
					<label>
						<input type="checkbox" name="exportFields[]" value="userInfo.fname" checked="checked"/> Voornaam
					</label>
				</div>
				<div class="large-6 medium-12 column">
					<label>
						<input type="checkbox" name="exportFields[]" value="userInfo.lname" checked="checked"/> Achternaam
					</label>
				</div>

				<div class="large-6 medium-12 column">
					<label>
						<input type="checkbox" name="exportFields[]" value="userInfo.schoolnr" checked="checked"/> R-nummer
					</label>
				</div>
				<div class="large-6 medium-12 column">
					<label>
						<input type="checkbox" name="exportFields[]" value="userInfo.email"/> E-mailadres
					</label>
				</div>

				<div class="large-6 medium-12 column">
					<label>
						<input type="checkbox" name="exportFields[]" value="userInfo.username"/> Gebruikersnaam
					</label>
				</div>
				<div class="large-6 medium-12 column">
					<label>
						<input type="checkbox" name="exportFields[]" value="userLog.time"/> Datum/tijd
					</label>
				</div>

				<div class="large-6 medium-12 column">
					<label>Facturatiestatus van geselecteerde items instellen:
						{{ Form::select
						(
							'boekhouding',
							array ( 'unchanged' => 'Ongewijzigd laten' ) + $boekhoudingBetekenis,
							'unchanged'
						)
						}}
					</label>
				</div>
				<div class="large-6 medium-12 column">
					<label>CSV scheidingsteken instellen:
						{{ Form::select
						(
							'seperator',
							array 
							(
								'Komma (,)',
								'Puntkomma (MS Excel) (;)'
							),
							0
						)
						}}
					</label>
				</div>
				<div class="large-6 medium-12 column">
					<label>
						<input type="submit" name="submit" value="Exporteren" class="button"/>
					</label>
				</div>
			</div>
		</div>
	</div>
</form>
@endsection
