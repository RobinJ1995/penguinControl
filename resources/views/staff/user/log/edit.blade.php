@extends ('layout.master')

@section ('pageTitle')
Edit billing entry
@endsection

@section ('content')
<form action="/staff/user/log/{{ $userlog->id }}/edit" method="POST" data-abide>
	<fieldset>
		<legend>Edit billing entry</legend>
		<div class="row">
			<div class="large-12 medium-12 small-12 column">
				<label>Status:
					{{ Form::select
						(
							'boekhouding',
							$boekhoudingBetekenis,
							$userlog->boekhouding
						)
					}}
				</label>
				<small class="error">Invalid input</small>
			</div>
		</div>
		<div>
			{{ Form::token () }}
			<button name="save" value="{{ $userlog->id }}">Save</button>
		</div>
	</fieldset>
</form>
@endsection
