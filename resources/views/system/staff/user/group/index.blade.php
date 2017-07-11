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
			<th>Name</th>
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
		</tr>
		@endforeach
	</tbody>
</table>
<div class="right">
	<a href="/staff/user/group/create" title="Add" class="button radius">
		<img src="/img/icons/add.png" alt="Add" />
	</a>
</div>
@endsection