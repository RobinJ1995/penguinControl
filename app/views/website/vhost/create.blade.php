@extends ('layout.master')

@section ('pageTitle')
vHost toevoegen
@endsection

@section ('content')
<form action="/website/vhost/create" method="POST" data-abide>
	<fieldset>
		<legend>vHost toevoegen</legend>
		<div>
			<label>Host:
				<div class="row collapse">
					<div class="large-4 medium-6 small-12 column">
						<input type="text" name="servername" value="{{ Input::old ('servername') }}" required />
					</div>
					<div class="large-8 medium-6 small-12 column">
						<span class="postfix">.{{ $userInfo->username }}.sinners.be</span>
					</div>
				</div>
				<small class="error">Verplicht veld</small>
			</label>
		</div>
		<div>
			<label>Document root:
				<div class="row collapse">
					<div class="large-4 medium-6 small-12 column">
						<span class="prefix">{{ $user->homedir }}/</span>
					</div>
					<div class="large-8 medium-6 small-12 column">
						<input type="text" name="docroot" value="{{ Input::old ('docroot') }}" />
					</div>
				</div>
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
						Input::old ('ssl', 0)
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
						Input::old ('cgi', 0)
					)
					}}
				</label>
				<small class="error">Ongeldige waarde</small>
			</div>
		</div>
		<div>
			{{ Form::token () }}
			<button name="save" value="{{ time () }}">Opslaan</button>
		</div>
	</fieldset>
</form>
@endsection