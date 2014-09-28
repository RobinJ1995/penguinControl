@extends ('layout.master')

@section ('pageTitle')
E-maildomeinen
@endsection

@section ('content')
<table>
	<thead>
		<tr>
			<th></th>
			<th>Domein</th>
		</tr>
	</thead>
	<tbody>
		@foreach ($domains as $domain)
		<tr>
			<td>
				<div class="button-group radius">
					<a href="/mail/domain/{{ $domain->id }}/edit" title="Bewerken" class="button tiny">
						<img src="/img/icons/edit.png" alt="Bewerken" />
					</a><a href="/mail/domain/{{ $domain->id }}/remove" title="Verwijderen" class="button tiny alert remove">
						<img src="/img/icons/remove.png" alt="Verwijderen" />
					</a>
				</div>
			</td>
			<td>{{ $domain->domain }}</td>
		</tr>
		@endforeach
	</tbody>
</table>
<div class="right">
	<a href="/mail/domain/create" title="Toevoegen" class="button radius">
		<img src="/img/icons/add.png" alt="Toevoegen" />
	</a>
</div>
@endsection