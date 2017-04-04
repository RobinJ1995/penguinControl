@extends ('layout.master')

@section ('pageTitle')
E-mail domains
@endsection

@section ('content')
<table>
	<thead>
		<tr>
			<th></th>
			<th>Domain</th>
		</tr>
	</thead>
	<tbody>
		@foreach ($domains as $domain)
		<tr>
			<td>
				<div class="button-group radius">
					<a href="/mail/domain/{{ $domain->id }}/edit" title="Edit" class="button tiny">
						<img src="/img/icons/edit.png" alt="Edit" />
					</a><a href="/mail/domain/{{ $domain->id }}/remove" title="Remove" class="button tiny alert remove">
						<img src="/img/icons/remove.png" alt="Remove" />
					</a>
				</div>
			</td>
			<td>{{ $domain->domain }}</td>
		</tr>
		@endforeach
	</tbody>
</table>
<div class="right">
	<a href="/mail/domain/create" title="Add" class="button radius">
		<img src="/img/icons/add.png" alt="Add" />
	</a>
</div>
@endsection