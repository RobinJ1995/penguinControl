@extends ('layout.master')

@section ('pageTitle')
User expiry date &bull; Staff
@endsection

@section ('content')
<form action="/staff/user/user/{{ $user->id }}/expire" method="POST" data-abide>
	<fieldset>
		<legend>Change expiry date</legend>
		<div class="row">
			<div class="large-4 medium-4 small-12 column">
				<label>Valid until:
					<input type="text" value="{{ $validUntilDate }}" disabled title="{{ $validUntilShortDate }}" />
				</label>
			</div>
			<div class="large-4 medium-4 small-12 column">
				<label>UNIX timestamp:
					<input type="text" value="{{ $validUntilUnix }}" disabled title="{{ $user->expire > 0 ? $user->expire . ' days since 1 January 1970' : '' }}" />
				</label>
			</div>
			<div class="large-4 medium-4 small-12 column">
				<label>Still valid for:
					<input type="text" value="{{ $stillValidDate }}" disabled title="{{ $user->expire > 0 ? $stillValidUnix . ' seconds' : '' }}" />
				</label>
			</div>
		</div>
		<div class="row">
			<div class="large-12 medium-12 small-12 column">
				<label>Expiry date:
					{{ Form::select
						(
							'expire',
							$expires,
							old ('expire', $validUntilUnix)
						)
					}}
				</label>
			</div>
		</div>
		<div>
			{{ Form::token () }}
			<button name="save" value="{{ $user->id }}">Save</button>
		</div>
	</fieldset>
</form>
@endsection