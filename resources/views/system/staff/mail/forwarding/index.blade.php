@extends ('layout.master')

@section ('pageTitle')
Forwarding addresses &bull; Staff
@endsection

@section ('content')
{{ $mFwds->links () }}
<table>
	<thead>
		<tr>
			<th></th>
			<th>E-mail address</th>
			<th>Destination</th>
			<th>User</th>
		</tr>
	</thead>
	<tbody>
		@foreach ($mFwds as $mFwd)
		<tr>
			<td>
				<div class="button-group radius">
					<a href="/staff/mail/forward/{{ $mFwd->id }}/edit" title="Edit" class="button tiny">
						<img src="/img/icons/edit.png" alt="Edit" />
					</a><a href="/staff/mail/forward/{{ $mFwd->id }}/remove" title="Remove" class="button tiny alert remove">
						<img src="/img/icons/remove.png" alt="Remove" />
					</a>
				</div>
			</td>
			<td>
				@if ($mFwd->mailDomainVirtual)
					@if ($mFwd->uid !== $mFwd->mailDomainVirtual->uid)
						<img src="/img/icons/locked.png" alt="[Locked]" />
					@endif
				@endif
				@if ($mFwd->user->hasExpired ())
					<img src="/img/icons/vhost-expired.png" alt="[Expired]" />
				@endif
				@if ($mFwd->mailDomainVirtual)
					{{$mFwd->source . '@' . $mFwd->mailDomainVirtual->domain}}
				@else
					{{ $mFwd->source }}
				@endif
			</td>
			<td>{{ $mFwd->destination }}</td>
			<td>
				<span class="{{ $mFwd->user->gid < Group::where ('name', 'user')->firstOrFail ()->gid ? 'label' : '' }}">{{ $mFwd->user->userInfo->username }}</span>
			</td>
		</tr>
		@endforeach
	</tbody>
</table>
{{ $mFwds->links () }}
<div class="right">
	<a href="/staff/mail/forward/create" title="Add" class="button radius">
		<img src="/img/icons/add.png" alt="Add" />
	</a>
</div>

@include ('staff.mail.search_part')
@endsection
