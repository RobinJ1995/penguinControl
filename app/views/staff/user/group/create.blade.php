@extends ('layout.master')

@section ('pageTitle')
Groep toevoegen &bull; Staff
@endsection

@section ('content')
<form action="/staff/user/group/create" method="POST" data-abide>
	<fieldset>
		<legend>Groep toevoegen</legend>
		<div class="row">
			<div class="large-3 medium-3 small-12 column">
				<label>GID:
					<input type="number" name="gid" value="{{ Input::old ('gid') }}" min="1" max="{{ 200 }}" required />
				</label>
				<small class="error">Ongeldige waarde</small>
			</div>
			<div class="large-5 medium-5 small-12 column">
				<label>Naam:
					<input type="text" name="name" value="{{ Input::old ('name') }}" required />
				</label>
				<small class="error">Ongeldige waarde</small>
			</div>
			<div class="large-4 medium-4 small-12 column">
				<p>De volgende GID's zijn reeds in gebruik en kunnen dus niet meer gebruikt worden:</p>
				<ul>
					@foreach ($gids as $gid)
					<li>{{ $gid }}</li>
					@endforeach
				</ul>
			</div>
		</div>
		<div>
			{{ Form::token () }}
			<button name="save" value="{{ time () }}">Opslaan</button>
		</div>
	</fieldset>
</form>
@endsection