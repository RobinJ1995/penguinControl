@extends ('layout.master')

@section ('pageTitle')
E-mail accounts &bull; Staff
@endsection

@section ('content')
{{ $mUsers->links () }}
<table>
	<thead>
		<tr>
			<th></th>
			<th>E-mail address</th>
			<th>User</th>
		</tr>
	</thead>
	<tbody>
		@foreach ($mUsers as $mUser)
		<tr>
			<td>
				<div class="button-group radius">
					<a href="/staff/mail/user/{{ $mUser->id }}/edit" title="Edit" class="button tiny">
						<img src="/img/icons/edit.png" alt="Edit" />
					</a><a href="/staff/mail/user/{{ $mUser->id }}/remove" title="Remove" class="button tiny alert remove">
						<img src="/img/icons/remove.png" alt="Remove" />
					</a>
				</div>
			</td>
			<td>
				@if ($mUser->mailDomainVirtual)
					@if ($mUser->uid !== $mUser->mailDomainVirtual->uid)
						<img src="/img/icons/locked.png" alt="[Locked]" />
					@endif
				@endif
				@if ($mUser->user->hasExpired ())
					<img src="/img/icons/vhost-expired.png" alt="[Expired]" />
				@endif
				@if($mUser->mailDomainVirtual)
					{{ $mUser->email. '@' . $mUser->mailDomainVirtual->domain }}
				@else
					{{ $mUser->email }} 
				@endif
			</td>
			<td>
				<span class="{{ $mUser->user->gid < Group::where ('name', 'user')->firstOrFail ()->gid ? 'label' : '' }}">{{ $mUser->user->userInfo->username }}</span>
			</td>
		</tr>
		@endforeach
	</tbody>
</table>
{{ $mUsers->links () }}
<div class="right">
	<a href="/staff/mail/user/create" title="Add" class="button radius">
		<img src="/img/icons/add.png" alt="Add" />
	</a>
</div>

@include ('staff.mail.search_part')
@endsection
