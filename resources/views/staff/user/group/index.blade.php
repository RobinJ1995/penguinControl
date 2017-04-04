@extends ('layout.master')

@section ('pageTitle')
Groepen &bull; Staff
@endsection

@section ('content')
<table>
	<thead>
		<tr>
			<th></th>
			<th>GID</th>
			<th>Naam</th>
			<th></th>
		</tr>
	</thead>
	<tbody>
		@foreach ($groups as $group)
		<tr>
			<td>
				<div class="button-group radius">
					<a href="/staff/user/group/{{ $group->id }}/remove" title="Verwijderen" class="button tiny alert remove confirm">
						<img src="/img/icons/remove.png" alt="Verwijderen" />
					</a>
				</div>
			</td>
			<td>{{ $group->gid }}</td>
			<td>{{ $group->name }}</td>
			<td>
				<img src="/img/icons/{{ $group->gid < Group::where ('name', 'user')->firstOrFail ()->gid ? 'sin.png" alt="Medewerker" title="Medewerker' : 'user.png" alt="User' }}" />
			</td>
		</tr>
		@endforeach
	</tbody>
</table>
<div class="right">
	<a href="/staff/user/group/create" title="Toevoegen" class="button radius">
		<img src="/img/icons/add.png" alt="Toevoegen" />
	</a>
</div>
@endsection