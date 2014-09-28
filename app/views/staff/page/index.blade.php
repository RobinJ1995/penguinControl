@extends ('layout.master')

@section ('pageTitle')
Pagina's &bull; Staff
@endsection

@section ('content')
<table>
	<thead>
		<tr>
			<th></th>
			<th>Titel</th>
			<th>Aangemaakt</th>
			<th>Gewijzigd</th>
			<th>Status</th>
		</tr>
	</thead>
	<tbody>
		@foreach ($pages as $page)
		<tr>
			<td>
				<div class="button-group radius">
					<a href="/staff/page/{{ $page->name }}/edit" title="Bewerken" class="button tiny">
						<img src="/img/icons/edit.png" alt="Bewerken" />
					</a><a href="/staff/page/{{ $page->name }}/remove" title="Verwijderen" class="button tiny alert remove">
						<img src="/img/icons/remove.png" alt="Verwijderen" />
					</a>
				</div>
			</td>
			<td title="{{ $page->name }}">
				<a href="/page/{{ $page->name }}">{{ $page->title }}</a>
			</td>
			<td>{{ $page->created_at }}</td>
			<td>{{ $page->updated_at }}</td>
			<td>
				@if ($page->published > 0)
					<img src="/img/icons/published-in-menu.png" alt="[1]" /> Gepubliceerd in menu
				@elseif ($page->published == 0)
					<img src="/img/icons/published.png" alt="[0]" /> Gepubliceerd
				@else
					<img src="/img/icons/draft.png" alt="[-1]" /> Concept
				@endif
			</td>
		</tr>
		@endforeach
	</tbody>
</table>
<div class="right">
	<a href="/staff/page/create" title="Toevoegen" class="button radius">
		<img src="/img/icons/add.png" alt="Toevoegen" />
	</a>
</div>
@endsection