@extends ('layout.master')

@section ('pageTitle')
FTP accounts
@endsection

@section ('content')
<table>
	<thead>
		<tr>
			<th></th>
			<th>Username</th>
			<th>Directory</th>
		</tr>
	</thead>
	<tbody>
		@foreach ($ftps as $ftp)
		<tr>
			<td>
				@if (! $ftp->locked)
				<div class="button-group radius">
					<a href="/ftp/{{ $ftp->id }}/edit" title="Bewerken" class="button tiny">
						<img src="/img/icons/edit.png" alt="Bewerken" />
					</a><a href="/ftp/{{ $ftp->id }}/remove" title="Verwijderen" class="button tiny alert remove">
						<img src="/img/icons/remove.png" alt="Verwijderen" />
					</a>
				</div>
				@endif
			</td>
			<td>{{ $ftp->user }}</td>
			<td>~/{{ substr ($ftp->dir, strlen ($user->homedir) + 1) }}</td>
		</tr>
		@endforeach
	</tbody>
</table>
<div class="right">
	<a href="/ftp/create" title="Add" class="button radius">
		<img src="/img/icons/add.png" alt="Add" />
	</a>
</div>
@endsection