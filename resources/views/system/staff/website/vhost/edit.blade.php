@extends ('layout.master')

@section ('pageTitle')
Edit vHost &bull; Staff
@endsection

@section ('content')
<form action="/staff/website/vhost/{{ $vhost->id }}/edit" method="POST" data-abide>
	<fieldset>
		<legend>Edit vHost</legend>
		<div>
			<label>Owner:
				{{ Form::select
				(
					'uid',
					$users,
					old ('uid', $vhost->uid)
				)
				}}
			</label>
		</div>
		<div>
			<label>Host:
				<input type="text" name="servername" value="{{ old ('servername', $vhost->servername) }}" required disabled/>
			</label>
			<small class="error">Required field</small>
		</div>
		<div>
			<label>Administrator:
				<input type="email" name="serveradmin" value="{{ old ('serveradmin', $vhost->serveradmin) }}" required />
			</label>
			<small class="error">Required field</small>
		</div>
		<div>
			<label>Alias:
				<input type="text" name="serveralias" value="{{ old ('serveralias', $vhost->serveralias) }}" />
			</label>
			<small class="error">Invalid input</small>
		</div>
		<div>
			<label>Document root:
				<input type="text" name="docroot" value="{{ old ('docroot', $vhost->docroot) }}" />
			</label>
			<small class="error">Invalid input</small>
		</div>
		<div>
			<label>Basedir:
				<input type="text" name="basedir" value="{{ old ('basedir', $vhost->basedir) }}" />
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
						old ('ssl', $vhost->ssl)
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
							'0' => 'Disabled',
							'1' => 'Enabled'
						),
						old ('cgi', $vhost->cgi)
					)
					}}
				</label>
				<small class="error">Invalid input</small>
			</div>
		</div>
		<div>
			{{ Form::token () }}
			<button name="save" value="{{ $vhost->id }}">Save</button>
		</div>
	</fieldset>
</form>
@endsection
