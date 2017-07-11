@extends ('layout.master')

@section ('pageTitle')
Forwarding addresses
@endsection

@section ('content')
<table>
	<thead>
		<tr>
			<th></th>
			<th>E-mail address</th>
			<th>Destination</th>
		</tr>
	</thead>
	<tbody>
		@foreach ($mFwds as $mFwd)
		<tr>
			<td>
				<div class="button-group radius">
					@if($mFwd->uid === $mFwd->mailDomain->uid)
					<a href="/mail/forward/{{ $mFwd->id }}/edit" title="Edit" class="button tiny">
						<img src="/img/icons/edit.png" alt="Edit" />
					</a><a href="/mail/forward/{{ $mFwd->id }}/remove" title="Remove" class="button tiny alert remove">
						<img src="/img/icons/remove.png" alt="Remove" />
					</a>
					@endif
				</div>
			</td>
			<td>
				@if ($mFwd->mailDomainVirtual)
					{{$mFwd->source . '@' . $mFwd->mailDomain->domain}}
				@else
					{{ $mFwd->source }}
				@endif
			</td>
			<td>{{ $mFwd->destination }}</td>
		</tr>
		@endforeach
	</tbody>
</table>
@section ('custom_fields')
@show
<div class="right">
	<a href="/mail/forward/create" title="Add" class="button radius">
		<img src="/img/icons/add.png" alt="Add" />
	</a>
</div>
@endsection