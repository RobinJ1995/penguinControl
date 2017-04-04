@extends ('layout.master')

@section ('pageTitle')
Gebruikershoudbaarheidsdatum &bull; Staff
@endsection

@section ('content')
<form action="/staff/user/user/{{ $user->id }}/expire" method="POST" data-abide>
	<fieldset>
		<legend>Houdbaarheidsdatum wijzigen</legend>
		<div class="row">
			<div class="large-4 medium-4 small-12 column">
				<label>Geldig tot:
					<input type="text" value="{{ $validUntilDate }}" disabled title="{{ $validUntilShortDate }}" />
				</label>
			</div>
			<div class="large-4 medium-4 small-12 column">
				<label>UNIX timestamp:
					<input type="text" value="{{ $validUntilUnix }}" disabled title="{{ $user->expire > 0 ? $user->expire . ' dagen sinds 1 januari 1970' : '' }}" />
				</label>
			</div>
			<div class="large-4 medium-4 small-12 column">
				<label>Nog geldig:
					<input type="text" value="{{ $stillValidDate }}" disabled title="{{ $user->expire > 0 ? $stillValidUnix . ' seconden' : '' }}" />
				</label>
			</div>
		</div>
		<div class="row">
			<div class="large-12 medium-12 small-12 column">
				<label>Vervaldatum:
					{{ Form::select
						(
							'expire',
							$expires,
							Input::old ('expire', $validUntilUnix)
						)
					}}
				</label>
			</div>
		</div>
		<div>
			{{ Form::token () }}
			<button name="save" value="{{ $user->id }}">Opslaan</button>
		</div>
	</fieldset>
</form>
@endsection