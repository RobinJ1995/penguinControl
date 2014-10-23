@extends ('layout.master')

@section ('pageTitle')
Gebruikers &bull; Staff
@endsection

@section ('content')
<fieldset>
	<legend>{{ $count }} zoekresultaten</legend>
	
	{{ $results->links () }}
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
			<?php $user = $userInfo->getUser (); ?>
			<tr>
				<td>
					<div class="button-group radius">
						<a href="/staff/user/user/{{ $user->id }}/login" title="Aanmelden als gebruiker" class="button tiny">
							<img src="/img/icons/login.png" alt="Login" />
						</a><a href="/staff/user/user/{{ $user->id }}/expire" title="Vervaldatum wijzigen" class="button tiny">
							<img src="/img/icons/expire.png" alt="Expire" />
						</a><a href="/staff/user/user/{{ $user->id }}/edit" title="Bewerken" class="button tiny">
							<img src="/img/icons/edit.png" alt="Bewerken" />
						</a><a href="/staff/user/user/{{ $user->id }}/remove" title="Verwijderen" class="button tiny alert remove confirm">
							<img src="/img/icons/remove.png" alt="Verwijderen" />
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
			@endforeach
		</tbody>
	</table>
	{{ $results->links () }}
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
		
		<button>Zoeken</button>
	</form>
	
	<a class="close-reveal-modal">&#215;</a>
</div>
@endsection