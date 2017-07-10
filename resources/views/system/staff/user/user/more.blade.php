@extends ('layout.master')

@section ('pageTitle')
Meer opties voor gebruiker &bull; Staff
@endsection

@section ('content')
<ul class="button-group even-2 staffUserMore">
	<li>
		<a href="/staff/user/user/{{ $user->id }}/login" title="Aanmelden als gebruiker" class="button">
			<img src="/img/icons/login.png" alt="" /> Aanmelden als {{ $userInfo->username }}
		</a>
	</li>
	<li>
		<a href="/staff/user/user/{{ $user->id }}/expire" title="Vervaldatum wijzigen" class="button">
			<img src="/img/icons/expire.png" alt="" /> Vervaldatum wijzigen
		</a>
	</li>
	<li>
		<a href="/staff/user/user/{{ $user->id }}/edit" title="Bewerken" class="button">
			<img src="/img/icons/edit.png" alt="" /> Bewerken
		</a>
	</li>
	<li>
		<a href="/staff/system/log/search?userId={{ $user->id }}" title="Logs" class="button">
			<img src="/img/icons/logs.png" alt="" /> Logs
		</a>
	</li>
	<li>
		<a href="/sudo-fix-problem/{{ $user->id }}" title="sudo fix-problem" class="button alert warning">
			<img src="/img/icons/sin.png" alt="" /> Probleem oplossen (experimenteel)
		</a>
	</li>
	<li>
		<a href="/staff/user/user/{{ $user->id }}/remove" title="Verwijderen" class="button alert remove confirm">
			<img src="/img/icons/remove.png" alt="" /> Gebruiker verwijderen
		</a>
	</li>
</ul>

<fieldset>
	<legend>Informatie</legend>
	<div class="row">
		<div class="large-2 medium-3 small-12 column">
			<label>UID:
				<input type="number" name="uid" value="{{ $user->uid }}" min="{{ $user->uid }}" max="{{ $user->uid }}" readonly />
			</label>
		</div>
		<div class="large-4 medium-9 small-12 column">
			<label>Gebruikersnaam:
				<input type="text" name="username" value="{{ $userInfo->username }}" readonly />
			</label>
		</div>
		<div class="large-6 medium-12 small-12 column">
			<label>Home directory:
				<input type="text" name="homedir" value="{{ $user->homedir }}" readonly />
			</label>
		</div>
	</div>
	<div class="row">
		<div class="large-4 medium-12 small-12 column">
			<label>E-mailadres:
				<input type="email" name="email" value="{{ $userInfo->email }}" readonly />
			</label>
		</div>
		<div class="large-4 medium-6 small-12 column">
			<label>Voornaam:
				<input type="text" name="fname" value="{{ $userInfo->fname }}" readonly />
			</label>
		</div>
		<div class="large-4 medium-6 small-12 column">
			<label>Achternaam:
				<input type="text" name="lname" value="{{ $userInfo->lname }}" readonly />
			</label>
		</div>
	</div>
	<div class="row">
		<div class="large-4 medium-4 small-12 column">
			<label>r-nummer:
				<input type="text" name="rnummer" value="{{ $userInfo->schoolnr }}" readonly />
			</label>
		</div>
		<div class="large-4 medium-4 small-12 column">
			<label>Shell:
				<input type="text" name="shell" value="{{ $user->shell }}" readonly />
			</label>
		</div>
		<div class="large-4 medium-4 small-12 column">
			<label>E-mail:
				<input type="text" name="shell" value="{{ $userMailEnabledPretty }}" readonly />
			</label>
		</div>
	</div>
	<div class="row">
		<div class="large-4 medium-4 small-12 column">
			<label>Hashing-algoritme:
				<input type="text" name="algorithm" value="{{ $cryptAlgorithmPretty }}" readonly />
			</label>
		</div>
		<div class="large-8 medium-8 small-12 column">
			<label>Gecos:
				<input type="text" name="gcos" value="{{ $user->gcos }}" readonly />
			</label>
		</div>
	</div>
	<div class="row">
		<div class="large-6 medium-6 small-12 column">
			<label>Validatielink (voor verlenging):
				@if (empty ($userInfo->validationcode))
				<p class="alert-box info">Gebruiker heeft geen verlenging aangevraagd</p>
				@else
				<input type="text" name="validationcode" value="https://sinners.be/user/{{ $user->id }}/expired/renew/{{ $userInfo->validationcode }}" readonly />
				@endif
			</label>
		</div>
		<div class="large-6 medium-6 small-12 column">
			<label>Eenmalige loginlink:
				@if (empty ($userInfo->logintoken))
					@if (empty ($user) || $userInfo->validated == 0)
					<p class="alert-box info">Gebruiker is nog niet gevalideerd</p>
					@elseif ($user->hasExpired ())
					<p class="alert-box info">Account vervallen. Stel een tijdelijk wachtwoord in voor de gebruiker waarmee deze zijn/haar account kan verlengen.</p>
					@else
					<br />
					<a href="/staff/user/user/{{ $user->id }}/more/loginToken" class="button radius"><img src="/img/icons/generate.png" alt="" /> Genereren</a>
					@endif
				@else
					<input type="text" name="logintoken" value="https://sinners.be/user/{{ $user->id }}/amnesia/login/{{ $userInfo->logintoken }}" readonly />
				@endif
			</label>
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
@endsection
