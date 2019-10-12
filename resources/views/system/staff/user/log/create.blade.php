@extends ('layout.master')

@section ('pageTitle')
Add billing entry
@endsection

@section ('content')
<form action="/staff/user/log/create" method="POST" data-abide>
	<fieldset>
		<legend>Add billing entry</legend>
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
				<label>New:
					{{ Form::select
						(
							'new',
							array
							(
								'0' => 'No',
								'1' => 'Yes',
							),
							Input::old ('new', 0)
						)
					}}
				</label>
				<small class="error">Invalid input</small>
			</div>
			<div class="large-4 medium-4 small-12 column">
				<label>Status:
					{{ Form::select
						(
							'status',
							$statusMeaning,
							Input::old ('status', 0)
						)
					}}
				</label>
				<small class="error">Invalid input</small>
			</div>
		</div>
		<div>
			{{ Form::token () }}
			<button name="save" value="{{ time () }}">Save</button>
		</div>
	</fieldset>
</form>
@endsection
