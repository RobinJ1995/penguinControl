@extends ('layout.master')

@section ('pageTitle')
vHost bewerken &bull; Staff
@endsection

@section ('content')
<form action="/staff/website/vhost/{{ $vhost->id }}/edit" method="POST" data-abide>
	<fieldset>
		<legend>vHost bewerken</legend>
		<div>
			<label>Eigenaar:
				{{ Form::select
				(
					'uid',
					$users,
					Input::old ('uid', $vhost->uid)
				)
				}}
			</label>
		</div>
		<div>
			<label>Host:
				<input type="text" name="servername" value="{{ Input::old ('servername', $vhost->servername) }}" required disabled/>
			</label>
			<small class="error">Verplicht veld</small>
		</div>
		<div>
			<label>Beheerder:
				<input type="email" name="serveradmin" value="{{ Input::old ('serveradmin', $vhost->serveradmin) }}" required />
			</label>
			<small class="error">Verplicht veld</small>
		</div>
		<div>
			<label>Alias:
				<input type="text" name="serveralias" value="{{ Input::old ('serveralias', $vhost->serveralias) }}" />
			</label>
			<small class="error">Ongeldige waarde</small>
		</div>
		<div>
			<label>Document root:
				<input type="text" name="docroot" value="{{ Input::old ('docroot', $vhost->docroot) }}" />
			</label>
			<small class="error">Ongeldige waarde</small>
		</div>
		<div>
			<label>Basedir:
				<input type="text" name="basedir" value="{{ Input::old ('basedir', $vhost->basedir) }}" />
			</label>
			<small class="error">Ongeldige waarde</small>
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
						Input::old ('ssl', $vhost->ssl)
					)
					}}
				</label>
				<small class="error">Ongeldige waarde</small>
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
						Input::old ('cgi', $vhost->cgi)
					)
					}}
				</label>
				<small class="error">Ongeldige waarde</small>
			</div>
		</div>
		<div>
			{{ Form::token () }}
			<button name="save" value="{{ $vhost->id }}">Opslaan</button>
		</div>
	</fieldset>
</form>
@endsection
