@extends ('layout.master')

@section ('pageTitle')
Gebruikers &bull; Staff
@endsection

@section ('content')
<div data-magellan-expedition="fixed">
	<dl class="sub-nav">
		<dd data-magellan-arrival="build">
			<a href="#users">Gebruikers ({{ $usersCount }})</a>
		</dd>
		<dd data-magellan-arrival="build">
			<a href="#expired">Vervallen ({{ $expiredCount }})</a>
		</dd>
		<dd data-magellan-arrival="js">
			<a href="#pending">Nog te valideren ({{ $pendingCount }})</a>
		</dd>
	</dl>
</div>

<fieldset>
	<legend id="users">Gebruikers</legend>
	<?php Paginator::setPageName ('user_page'); ?>
	{{ $users->links () }}
	<table>
		<thead>
			<tr>
				<th></th>
				<th>
					<a href="{{ $url }}/order/uid">UID</a>
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
					<a href="{{ $url }}/order/gid">Primaire groep</a>
				</th>
			</tr>
		</thead>
		<tbody>
			@foreach ($users as $user)
			<tr>
				<td>
					<div class="button-group radius">
						<a href="/staff/user/user/{{ $user->id }}/more" title="Meer..." class="button tiny">
							<img src="/img/icons/more.png" alt="Meer..." />
						</a><a href="/staff/user/user/{{ $user->id }}/expire" title="Vervaldatum wijzigen" class="button tiny">
							<img src="/img/icons/expire.png" alt="Expire" />
						</a><a href="/staff/user/user/{{ $user->id }}/edit" title="Bewerken" class="button tiny">
							<img src="/img/icons/edit.png" alt="Bewerken" />
						</a>
					</div>
				</td>	
				<td>{{ $user->uid }}</td>
				<td>{{ $user->userInfo->username }}</td>
				<td>{{ $user->userInfo->getFullName () }}</td>
				<td>{{ $user->userInfo->schoolnr }}</td>
				<td>
					<span class="{{ $user->gid < Group::where ('name', 'user')->firstOrFail ()->gid ? 'label' : '' }}">{{ ucfirst ($user->primaryGroup->name) }}</span>
				</td>
			</tr>
			@endforeach
		</tbody>
	</table>
	{{ $users->links () }}
	<div class="right">
		<a href="/staff/user/user/create" title="Toevoegen" class="button radius">
			<img src="/img/icons/add.png" alt="Toevoegen" />
		</a>
	</div>
</fieldset>

<fieldset>
	<legend id="expired">Vervallen</legend>
	<?php Paginator::setPageName ('expired_page'); ?>
	{{ $expired->links () }}
	<table>
		<thead>
			<tr>
				<th></th>
				<th>
					<a href="{{ $url }}/order/uid">UID</a>
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
					<a href="{{ $url }}/order/gid">Primaire groep</a>
				</th>
			</tr>
		</thead>
		<tbody>
			@foreach ($expired as $user)
			<tr class="expired">
				<td>
					<div class="button-group radius">
						<a href="/staff/user/user/{{ $user->id }}/more" title="Meer..." class="button tiny">
							<img src="/img/icons/more.png" alt="Meer..." />
						</a><a href="/staff/user/user/{{ $user->id }}/expire" title="Vervaldatum wijzigen" class="button tiny alert">
							<img src="/img/icons/expire.png" alt="Expire" />
						</a><a href="/staff/user/user/{{ $user->id }}/edit" title="Bewerken" class="button tiny">
							<img src="/img/icons/edit.png" alt="Bewerken" />
						</a>
					</div>
				</td>
				<td>{{ $user->uid }}</td>
				<td>{{ $user->userInfo->username }}</td>
				<td>{{ $user->userInfo->getFullName () }}</td>
				<td>{{ $user->userInfo->schoolnr }}</td>
				<td>
					<span class="{{ $user->gid < Group::where ('name', 'user')->firstOrFail ()->gid ? 'label' : '' }}">{{ ucfirst ($user->primaryGroup->name) }}</span>
				</td>
			</tr>
			@endforeach
		</tbody>
	</table>
	{{ $expired->links () }}
</fieldset>

<fieldset>
	<legend id="pending">Nog te valideren</legend>
	<?php Paginator::setPageName ('pending_page'); ?>
	{{ $pending->links () }}
	<table>
		<thead>
			<tr>
				<th></th>
				<th>Gebruikersnaam</th>
				<th>Naam</th>
				<th>E-mailadres</th>
				<th>r-nummer</th>
			</tr>
		</thead>
		<tbody>
			@foreach ($pending as $user) {{-- Let op; $user is hier UserInfo, niet User --}}
			<tr>
				<td>
					<div class="button-group radius">
						<a href="/staff/user/user/{{ $user->id }}/validate" title="Valideren" class="button tiny">
							<img src="/img/icons/validate.png" alt="Valideren" />
						</a><a href="/staff/user/user/{{ $user->id }}/reject" title="Weigeren" class="button tiny alert remove confirm">
							<img src="/img/icons/reject.png" alt="Weigeren" />
						</a>
					</div>
				</td>
				<td>{{ $user->username }}</td>
				<td>{{ $user->getFullName () }}</td>
				<td>{{ $user->email }}</td>
				<td>{{ $user->schoolnr }}</td>
			</tr>
			@endforeach
		</tbody>
	</table>
	{{ $pending->links () }}
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