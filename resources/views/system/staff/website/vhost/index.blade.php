@extends ('layout.master')

@section ('pageTitle')
vHosts &bull; Staff
@endsection

@section ('content')
{{ $vhosts->links () }}
<table>
	<thead>
		<tr>
			<th></th>
			<th>Host</th>
			<th>Beheerder</th>
			<th>Alias</th>
			<th>Gebruiker</th>
		</tr>
	</thead>
	<tbody>
		@foreach ($vhosts as $vhost)
		<tr>
			<td>
				<div class="button-group radius">
					<a href="/staff/website/vhost/{{ $vhost->id }}/edit" title="Bewerken" class="button tiny">
						<img src="/img/icons/edit.png" alt="Bewerken" />
					</a><a href="/staff/website/vhost/{{ $vhost->id }}/remove" title="Verwijderen" class="button tiny alert remove">
						<img src="/img/icons/remove.png" alt="Verwijderen" />
					</a>
				</div>
			</td>
			<td>
				@if ($vhost->locked)
					<img src="/img/icons/locked.png" alt="[Locked]" />
				@endif
				@if ($vhost->user->hasExpired ())
					<img src="/img/icons/vhost-expired.png" alt="[Expired]" />
				@endif
				{{ $vhost->servername }}
			</td>
			<td>{{ $vhost->serveradmin }}</td>
			<td>{{ $vhost->serveralias }}</td>
			<td>
				<span class="{{ $vhost->user->gid < Group::where ('name', 'user')->firstOrFail ()->gid ? 'label' : '' }}">{{ $vhost->user->userInfo->username }}</span>
			</td>
		</tr>
		@endforeach
	</tbody>
</table>
{{ $vhosts->links () }}
<div class="right">
	<a href="/staff/website/vhost/create" title="Toevoegen" class="button radius">
		<img src="/img/icons/add.png" alt="Toevoegen" />
	</a>
</div>

<div id="modalSearch" class="reveal-modal" data-reveal>
	<h2>Zoeken</h2>
	
	<form action="{{ $searchUrl }}" method="GET">
		<label>Host:
			<input type="text" name="host" />
		</label>
		<label>Document root:
			<input type="text" name="docroot" />
		</label>
		<label>Basedir:
			<input type="text" name="basedir" />
		</label>
		<label>Gebruiker:
			<input type="text" name="username" />
		</label>
		
		<button>Zoeken</button>
	</form>
	
	<a class="close-reveal-modal">&#215;</a>
</div>
@endsection