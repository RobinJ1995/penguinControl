@extends ('layout.master')

@section ('pageTitle')
Meer opties voor gebruiker &bull; Staff
@endsection

@section ('content')
<form action="/staff/user/user/{{ $user->id }}/more" method="POST" data-abide>
	<div class="button-group radius even-3 stack-for-small">
		<a href="/staff/user/user/{{ $user->id }}/login" title="Aanmelden als gebruiker" class="button">
			<img src="/img/icons/login.png" alt="" /> Aanmelden als {{ $userInfo->username }}
		</a><a href="/staff/user/user/{{ $user->id }}/expire" title="Vervaldatum wijzigen" class="button">
			<img src="/img/icons/expire.png" alt="" /> Vervaldatum wijzigen
		</a><a href="/staff/user/user/{{ $user->id }}/edit" title="Bewerken" class="button">
			<img src="/img/icons/edit.png" alt="" /> Bewerken
		</a><a href="/staff/user/user/{{ $user->id }}/remove" title="Verwijderen" class="button alert remove confirm">
			<img src="/img/icons/remove.png" alt="" /> Gebruiker verwijderen
		</a>
	</div>
	
	<fieldset>
		<legend>Informatie</legend>
		<div class="row">
			<div class="large-2 medium-3 small-12 column">
				<label>UID:
					<input type="number" name="uid" value="{{ $user->uid }}" min="{{ $user->uid }}" max="{{ $user->uid }}" readonly />
				</label>
				<small class="error">Afblijven!</small>
			</div>
			<div class="large-4 medium-9 small-12 column">
				<label>Gebruikersnaam:
					<input type="text" name="username" value="{{ $userInfo->username }}" readonly />
				</label>
				<small class="error">Afblijven!</small>
			</div>
			<div class="large-6 medium-12 small-12 column">
				<label>Home directory:
					<input type="text" name="homedir" value="{{ $user->homedir }}" readonly />
				</label>
				<small class="error">Afblijven!</small>
			</div>
		</div>
		<div class="row">
			<div class="large-4 medium-12 small-12 column">
				<label>E-mailadres:
					<input type="email" name="email" value="{{ $userInfo->email }}" readonly />
				</label>
				<small class="error">Ongeldige waarde</small>
			</div>
			<div class="large-4 medium-6 small-12 column">
				<label>Voornaam:
					<input type="text" name="fname" value="{{ $userInfo->fname }}" readonly />
				</label>
				<small class="error">Ongeldige waarde</small>
			</div>
			<div class="large-4 medium-6 small-12 column">
				<label>Achternaam:
					<input type="text" name="lname" value="{{ $userInfo->lname }}" readonly />
				</label>
				<small class="error">Ongeldige waarde</small>
			</div>
		</div>
		<div class="row">
			<div class="large-4 medium-4 small-12 column">
				<label>r-nummer:
					<input type="text" name="rnummer" value="{{ $userInfo->schoolnr }}" readonly />
				</label>
				<small class="error">Ongeldige waarde</small>
			</div>
			<div class="large-4 medium-4 small-12 column">
				<label>Shell:
					<input type="text" name="shell" value="{{ $user->shell }}" readonly />
				</label>
				<small class="error">Ongeldige waarde</small>
			</div>
			<div class="large-4 medium-4 small-12 column">
				<label>E-mail:
					<input type="text" name="shell" value="{{ $userMailEnabledPretty }}" readonly />
				</label>
				<small class="error">Ongeldige waarde</small>
			</div>
		</div>
		<div class="row">
			<div class="large-4 medium-4 small-12 column">
				<label>Hashing-algoritme:
					<input type="text" name="algorithm" value="{{ $cryptAlgorithmPretty }}" readonly />
				</label>
				<small class="error">Ongeldige waarde</small>
			</div>
			<div class="large-8 medium-8 small-12 column">
				<label>Gecos:
					<input type="text" name="gcos" value="{{ $user->gcos }}" readonly />
				</label>
				<small class="error">Ongeldige waarde</small>
			</div>
		</div>
		<div class="row">
			<div class="large-6 medium-6 small-12 column">
				<label>Validatielink (voor verlenging):
					@if (empty ($userInfo->validationcode))
					<br /><a href="#" class="button radius"><img src="/img/icons/generate.png" alt="" /> Genereren</a>
					@else
					<input type="text" name="validationcode" value="https://sinners.be/user/{{ $user->id }}/expired/renew/{{ $userInfo->validationcode }}" readonly />
					@endif
				</label>
				<small class="error">Ongeldige waarde</small>
			</div>
			<div class="large-6 medium-6 small-12 column">
				<label>Eenmalige loginlink:
					@if (empty ($userInfo->logintoken))
					<br /><a href="#" class="button radius"><img src="/img/icons/generate.png" alt="" /> Genereren</a>
					@else
					<input type="text" name="logintoken" value="https://sinners.be/user/{{ $user->id }}/amnesia/login/{{ $userInfo->logintoken }}" readonly />
					@endif
				</label>
				<small class="error">Ongeldige waarde</small>
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
								{{ $user->gid == $group->gid ? '<img src="/img/icons/ok.png" alt="Lid" />' : '' }}
							</td>
							<td>
								{{ $user->isGroupMember ($group) ? '<img src="/img/icons/ok.png" alt="Lid" />' : '' }}
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
	</fieldset>
</form>
@endsection
