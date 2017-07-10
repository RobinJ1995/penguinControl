@extends ('layout.master')

@section ('pageTitle')
FTP-accounts &bull; Staff
@endsection

@section ('content')
<fieldset>
	<legend>{{ $count }} zoekresultaten</legend>
	
	{{ $ftps->links () }}
	<table>
		<thead>
			<tr>
				<th></th>
				<th>Gebruikersnaam</th>
				<th>Map</th>
				<th>Eigenaar</th>
			</tr>
		</thead>
		<tbody>
			@foreach ($ftps as $ftp)
			<tr>
				<td>
					<div class="button-group radius">
						<a href="/staff/ftp/{{ $ftp->id }}/edit" title="Bewerken" class="button tiny">
							<img src="/img/icons/edit.png" alt="Bewerken" />
						</a><a href="/staff/ftp/{{ $ftp->id }}/remove" title="Verwijderen" class="button tiny alert remove">
							<img src="/img/icons/remove.png" alt="Verwijderen" />
						</a>
					</div>
				</td>
				<td>
					@if ($ftp->locked)
						<img src="/img/icons/locked.png" alt="[Locked]" />
					@endif
					{{ $ftp->user }}
				</td>
				<td>{{ $ftp->dir }}</td>
				<td>
					<span class="{{ $ftp->user->gid < Group::where ('name', 'user')->firstOrFail ()->gid ? 'label' : '' }}">{{ $ftp->user->userInfo->username }}</span>
				</td>
			</tr>
			@endforeach
		</tbody>
	</table>
	{{ $ftps->links () }}
</fieldset>

<div id="modalSearch" class="reveal-modal" data-reveal>
	<h2>Zoeken</h2>
	
	<form action="{{ $searchUrl }}" method="GET">
		<label>Gebruikernaam:
			<input type="text" name="user" />
		</label>
		<label>Map:
			<input type="text" name="dir" />
		</label>
		<label>Gebruiker:
			<input type="text" name="username" />
		</label>
		
		<button>Zoeken</button>
	</form>
	
	<a class="close-reveal-modal">&#215;</a>
</div>
@endsection