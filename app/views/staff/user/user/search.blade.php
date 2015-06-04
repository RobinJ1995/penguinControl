@extends ('layout.master')

@section ('pageTitle')
Gebruikers &bull; Staff
@endsection

@section ('content')
<fieldset>
	<legend>{{ $count }} zoekresultaten</legend>
	
	{{ $results->appends (Input::all ())->links () }}
	<table>
		<thead>
			<tr>
				<th></th>
				<th>
					UID
				</th>
				<th>
					Gebruikersnaam
				</th>
				<th>
					Naam
				</th>
				<th>
					r-nummer
				</th>
				<th>
					Primaire groep
				</th>
			</tr>
		</thead>
		<tbody>
			@foreach ($results as $userInfo)
			<?php $user = $userInfo->user; ?>
			@if (empty ($user))
			<tr>
				<td colspan="2">Geen bijhorende gebruiker<br />
					(<kbd>user_info#{{ $userInfo->id }}</kbd>)</td>
				<td>{{ $userInfo->username }}</td>
				<td>{{ $userInfo->getFullName () }}</td>
				<td>{{ $userInfo->schoolnr }}</td>
				<td></td>
			</tr>
			@else
			<tr>
				<td>
					<div class="button-group radius">
						<a href="/staff/user/user/{{ $user->id }}/more" title="Meer..." class="button tiny">
							<img src="/img/icons/more.png" alt="Meer..." />
						</a><a href="/staff/user/user/{{ $user->id }}/expire" title="Vervaldatum wijzigen" class="button tiny {{ $user->hasExpired () ? 'alert' : '' }}">
							<img src="/img/icons/expire.png" alt="Expire" />
						</a><a href="/staff/user/user/{{ $user->id }}/edit" title="Bewerken" class="button tiny">
							<img src="/img/icons/edit.png" alt="Bewerken" />
						</a>
					</div>
				</td>
				<td>{{ $user->uid }}</td>
				<td>{{ $userInfo->username }}</td>
				<td>{{ $userInfo->getFullName () }}</td>
				<td>{{ $userInfo->schoolnr }}</td>
				<td>
					<span class="{{ $user->gid < Group::where ('name', 'user')->firstOrFail ()->gid ? 'label' : '' }}">{{ ucfirst ($user->getGroup ()->name) }}</span>
				</td>
			</tr>
			@endif
			@endforeach
		</tbody>
	</table>
	{{ $results->appends (Input::all ())->links () }}
</fieldset>

<div id="modalSearch" class="reveal-modal" data-reveal>
	<h2>Zoeken</h2>
	
	<form action="{{ $searchUrl }}" method="GET">
		<label>Gebruikersnaam:
			<input type="text" name="username" />
		</label>
		<label>Naam:
			<input type="text" name="name" />
		</label>
		<label>E-mailadres:
			<input type="text" name="email" />
		</label>
		<label>Studentnummer:
			<input type="text" name="schoolnr" />
		</label>
		<label>
			<input type="checkbox" name="validationcode" /> Heeft ongebruikte validatiecode voor verlenging
		</label>
		<label>
			<input type="checkbox" name="logintoken" /> Heeft ongebruikte eenmalige loginlink
		</label>
		
		<button>Zoeken</button>
	</form>
	
	<a class="close-reveal-modal">&#215;</a>
</div>
@endsection