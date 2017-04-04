@extends ('layout.master')

@section ('pageTitle')
Gebruiker toevoegen &bull; Staff
@endsection

@section ('content')
<form action="/staff/user/user/create" method="POST" data-abide>
	<fieldset>
		<legend>Gebruiker toevoegen</legend>
		<div class="row">
			<div class="large-2 medium-3 small-12 column">
				<label>UID:
					<input type="number" name="uid" value="{{ Input::old ('uid', $uid) }}" min="{{ $uid }}" max="{{ $uid }}" required />
				</label>
				<small class="error">Invalid input</small>
			</div>
			<div class="large-4 medium-9 small-12 column">
				<label>Gebruikersnaam:
					<input type="text" name="username" value="{{ Input::old ('username') }}" required />
				</label>
				<small class="error">Invalid input</small>
			</div>
			<div class="large-6 medium-12 small-12 column">
				<label>Home directory:
					<input type="text" name="homedir" value="{{ Input::old ('homedir') }}" required />
				</label>
				<small class="error">Invalid input</small>
			</div>
		</div>
		<div class="row">
			<div class="large-4 medium-12 small-12 column">
				<label>E-mailadres:
					<input type="email" name="email" value="{{ Input::old ('email') }}" required />
				</label>
				<small class="error">Invalid input</small>
			</div>
			<div class="large-4 medium-6 small-12 column">
				<label>Voornaam:
					<input type="text" name="fname" value="{{ Input::old ('fname') }}" required />
				</label>
				<small class="error">Invalid input</small>
			</div>
			<div class="large-4 medium-6 small-12 column">
				<label>Achternaam:
					<input type="text" name="lname" value="{{ Input::old ('lname') }}" required />
				</label>
				<small class="error">Invalid input</small>
			</div>
		</div>
		<div class="row">
			<div class="large-4 medium-4 small-12 column">
				<label>r-nummer:
					<input type="text" name="rnummer" value="{{ Input::old ('rnummer') }}" required />
				</label>
				<small class="error">Invalid input</small>
			</div>
			<div class="large-4 medium-4 small-12 column">
				<label>Shell:
					{{ Form::select
						(
							'shell',
							array
							(
								'/bin/bash' => 'Bash',
								'/bin/fish' => 'Fish',
								'/bin/zsh' => 'ZSH',
								'/usr/bin/tmux' => 'Tmux',
								'/bin/false' => 'Blokkeer toegang (/bin/false)'
							),
							Input::old ('shell', '/bin/bash')
						)
					}}
				</label>
				<small class="error">Invalid input</small>
			</div>
			<div class="large-4 medium-4 small-12 column">
				<label>E-mail:
					{{ Form::select
						(
							'mailEnabled',
							array
							(
								'0' => 'Uit',
								'1' => 'Aan',
								'-1' => 'Blokkeren'
							),
							Input::old ('mailEnabled', 0)
						)
					}}
				</label>
				<small class="error">Invalid input</small>
			</div>
		</div>
		<div class="row">
			<div class="large-6 medium-6 small-12 column">
				<label>Wachtwoord:
					<input type="password" name="password" id="newPass" required />
				</label>
				<small class="error">Invalid input</small>
			</div>
			<div class="large-6 medium-6 small-12 column">
				<label>Wachtwoord (bevestiging):
					<input type="password" name="password_confirm" data-equalto="newPass" required />
				</label>
				<small class="error">Invalid input</small>
			</div>
		</div>
		<div class="row">
			<div class="large-12 column">
			<fieldset>
				<legend>Groep</legend>
				<table>
					<thead>
						<tr>
							<th>Primair</th>
							<th>Lid</th>
							<th>Groep</th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						@foreach ($groups as $group)
						<tr>
							<td>
								<input type="radio" name="groupPrimary" value="{{ $group->gid }}" />
							</td>
							<td>
								<input type="checkbox" name="groups[]" value="{{ $group->gid }}" />
							</td>
							<td>
								{{ ucfirst ($group->name) }}
							</td>
							<td>
								<img src="/img/icons/{{ $group->gid < Group::where ('name', 'user')->firstOrFail ()->gid ? 'sin.png" alt="Medewerker" title="Medewerker' : 'user.png" alt="User' }}" />
							</td>
						</tr>
						@endforeach
					</tbody>
				</table>
			</fieldset>
			</div>
		</div>
		<div>
			{{ Form::token () }}
			<button name="save" value="{{ time () }}">Opslaan</button>
		</div>
	</fieldset>
</form>
@endsection