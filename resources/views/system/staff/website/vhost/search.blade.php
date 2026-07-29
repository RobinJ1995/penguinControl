@extends ('layout.master')

@section ('pageTitle')
vHosts &bull; Staff
@endsection

@section ('content')
<fieldset>
	<legend>{{ $count }} search results</legend>
	
	{{ $vhosts->links () }}
	<table>
		<thead>
			<tr>
				<th></th>
				<th>Host</th>
				<th>Administrator</th>
				<th>Alias</th>
				<th>User</th>
			</tr>
		</thead>
		<tbody>
			@foreach ($vhosts as $vhost)
			<tr>
				<td>
					<div class="button-group radius">
						<a href="/staff/website/vhost/{{ $vhost->id }}/edit" title="Edit" class="button tiny">
							<img src="/img/icons/edit.png" alt="Edit" />
						</a><a href="/staff/website/vhost/{{ $vhost->id }}/remove" title="Remove" class="button tiny alert remove">
							<img src="/img/icons/remove.png" alt="Remove" />
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
</fieldset>

<div id="modalSearch" class="reveal-modal" data-reveal>
	<h2>Search</h2>
	
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
		<label>User:
			<input type="text" name="username" />
		</label>
		
		<button>Search</button>
	</form>
	
	<a class="close-reveal-modal">&#215;</a>
</div>
@endsection