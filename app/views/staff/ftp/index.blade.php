@extends ('layout.master')

@section ('pageTitle')
FTP-accounts &bull; Staff
@endsection

@section ('content')
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
				<span class="{{ $ftp->getUser ()->gid < Group::where ('name', 'user')->firstOrFail ()->gid ? 'label' : '' }}">{{ $ftp->getUser ()->getUserInfo ()->username }}</span>
			</td>
		</tr>
		@endforeach
	</tbody>
</table>
{{ $ftps->links () }}
<div class="right">
	<a href="/staff/ftp/create" title="Toevoegen" class="button radius">
		<img src="/img/icons/add.png" alt="Toevoegen" />
	</a>
</div>
@endsection