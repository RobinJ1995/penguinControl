@extends ('layout.master')

@section ('pageTitle')
	Create group
@endsection

@section ('alerts')
	@parent
	{!! new Alert ('Groups with a GID lower than 1100 are considered to have special privileges and users in such groups will gain access to at least some administrative functionality.', Alert::TYPE_WARNING) !!}
@endsection

@section ('content')
<form action="/staff/user/group/create" method="POST" data-abide>
	<fieldset>
		<legend>Create group</legend>
		<div class="row">
			<div class="large-3 medium-3 small-12 column">
				<label>GID:
					<input type="number" name="gid" value="{{ Input::old ('gid') }}" required />
				</label>
				<small class="error">Invalid input</small>
			</div>
			<div class="large-5 medium-5 small-12 column">
				<label>Name:
					<input type="text" name="name" value="{{ Input::old ('name') }}" required />
				</label>
				<small class="error">Invalid input</small>
			</div>
			<div class="large-4 medium-4 small-12 column">
				<p>The following GIDs are already in use and thus can no longer be used:</p>
				<ul>
					@foreach ($gids as $gid)
					<li>{{ $gid }}</li>
					@endforeach
				</ul>
			</div>
		</div>
		<div>
			{{ Form::token () }}
			<button name="save" value="{{ time () }}">Save</button>
		</div>
	</fieldset>
</form>
@endsection