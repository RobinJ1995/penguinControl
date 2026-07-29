@extends ('layout.master')

@section ('pageTitle')
Pages &bull; Staff
@endsection

@section ('content')
<table>
	<thead>
		<tr>
			<th></th>
			<th>Title</th>
			<th>Created</th>
			<th>Modified</th>
			<th>Status</th>
		</tr>
	</thead>
	<tbody>
		@foreach ($pages as $page)
		<tr>
			<td>
				<div class="button-group radius">
					<a href="/staff/page/{{ $page->name }}/edit" title="Edit" class="button tiny">
						<img src="/img/icons/edit.png" alt="Edit" />
					</a><a href="/staff/page/{{ $page->name }}/remove" title="Remove" class="button tiny alert remove">
						<img src="/img/icons/remove.png" alt="Remove" />
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
					<img src="/img/icons/published-in-menu.png" alt="[1]" /> Published in the menu
				@elseif ($page->published == 0)
					<img src="/img/icons/published.png" alt="[0]" /> Published
				@else
					<img src="/img/icons/draft.png" alt="[-1]" /> Draft
				@endif
			</td>
		</tr>
		@endforeach
	</tbody>
</table>
<div class="right">
	<a href="/staff/page/create" title="Add" class="button radius">
		<img src="/img/icons/add.png" alt="Add" />
	</a>
</div>
@endsection