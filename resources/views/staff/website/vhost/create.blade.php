@extends ('layout.master')

@section ('pageTitle')
vHost toevoegen &bull; Staff
@endsection

@section ('content')
<form action="/staff/website/vhost/create" method="POST" data-abide>
	<fieldset>
		<legend>vHost toevoegen</legend>
		<div>
			<label>Eigenaar:
				{{ Form::select
				(
					'uid',
					$users,
					Input::old ('uid', $user->uid)
				)
				}}
			</label>
		</div>
		<div>
			<label>Host:
				<input type="text" name="servername" value="{{ Input::old ('servername') }}" required />
			</label>
			<small class="error">Required field</small>
		</div>
		<div>
			<label>Beheerder:
				<input type="email" name="serveradmin" value="{{ Input::old ('serveradmin') }}" required />
			</label>
			<small class="error">Required field</small>
		</div>
		<div>
			<label>Alias:
				<input type="text" name="serveralias" value="{{ Input::old ('serveralias') }}" />
			</label>
			<small class="error">Invalid input</small>
		</div>
		<div>
			<label>Document root:
				<input type="text" name="docroot" value="{{ Input::old ('docroot') }}" />
			</label>
			<small class="error">Invalid input</small>
		</div>
		<div>
			<label>Basedir:
				<input type="text" name="basedir" value="{{ Input::old ('basedir') }}" />
			</label>
			<small class="error">Invalid input</small>
		</div>
		<div class="row">
			<div class="large-6 medium-7 small-12 column">
				<label>Protocol:
					{{ Form::select
					(
						'ssl',
						array
						(
							'0' => 'HTTP',
							'1' => 'Enkel HTTPS',
							'2' => 'HTTPS met redirect'
						),
						Input::old ('ssl', 0)
					)
					}}
				</label>
				<small class="error">Invalid input</small>
			</div>
			<div class="large-6 medium-5 small-12 column">
				<label>CGI:
					{{ Form::select
					(
						'cgi',
						array
						(
							'0' => 'Uit',
							'1' => 'Aan'
						),
						Input::old ('cgi', 0)
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
