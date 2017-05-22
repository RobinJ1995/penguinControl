@extends ('layout.master')

@section ('pageTitle')
Export billing report
@endsection

@section ('content')
<form action="/staff/user/log/export" method="POST" data-abide>
	<div class="row">
		<div class="large-12 column">
			<h2>Export</h2>
			<div class="row">
				<input type="hidden" name="userLogId" value='{{ json_encode ($userLogsIds) }}'/>
				<div class="large-6 medium-12 column">
					<label>
						<input type="checkbox" name="exportFields[]" value="userInfo.fname" checked="checked" /> First name
					</label>
				</div>
				<div class="large-6 medium-12 column">
					<label>
						<input type="checkbox" name="exportFields[]" value="userInfo.lname" checked="checked" /> Surname
					</label>
				</div>
				
				<div class="large-6 medium-12 column">
					<label>
						<input type="checkbox" name="exportFields[]" value="userInfo.username" checked="checked" /> Username
					</label>
				</div>
				<div class="large-6 medium-12 column">
					<label>
						<input type="checkbox" name="exportFields[]" value="userInfo.email"/> E-mail address
					</label>
				</div>
				
				<div class="large-12 medium-12 column">
					<label>
						<input type="checkbox" name="exportFields[]" value="userLog.time" checked="checked" /> Date/Time
					</label>
				</div>

				<div class="large-6 medium-12 column">
					<label>Set billing status of exported items:
						{{ Form::select
						(
							'boekhouding',
							array ( 'unchanged' => 'Don\'t change' ) + $boekhoudingBetekenis,
							'unchanged'
						)
						}}
					</label>
				</div>
				<div class="large-6 medium-12 column">
					<label>CSV field separation character:
						{{ Form::select
						(
							'seperator',
							array 
							(
								'Comma (,)',
								'Semicolon (MS Excel) (;)'
							),
							0
						)
						}}
					</label>
				</div>
				{{ csrf_field () }}
				<div class="large-6 medium-12 column">
					<input type="submit" name="submit" value="Export" class="button"/>
				</div>
			</div>
		</div>
	</div>
</form>
@endsection
