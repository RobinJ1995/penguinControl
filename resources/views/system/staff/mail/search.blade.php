@extends ('layout.master')

@section ('pageTitle')
E-mail domains and addresses &bull; Staff
@endsection

@section ('content')
{{ $mUsers->links () }}
<fieldset>
	<legend>{{ $mUsersCount }} e-mail accounts found</legend>
	
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
						{{ $mUser->email . '@' . $mUser->mailDomainVirtual->domain }}
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
</fieldset>

<fieldset>
	<legend>{{ $mFwdsCount }} forwarding addresses found</legend>
	
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
</fieldset>

<fieldset>
	<legend>{{ $domainsCount }} e-mail domains found</legend>
	
	{{ $domains->links () }}
	<table>
		<thead>
			<tr>
				<th></th>
				<th>Domain</th>
				<th>User</th>
			</tr>
		</thead>
		<tbody>
			@foreach ($domains as $domain)
			<tr>
				<td>
					<div class="button-group radius">
						<a href="/staff/mail/domain/{{ $domain->id }}/edit" title="Edit" class="button tiny">
							<img src="/img/icons/edit.png" alt="Edit" />
						</a><a href="/staff/mail/domain/{{ $domain->id }}/remove" title="Remove" class="button tiny alert remove">
							<img src="/img/icons/remove.png" alt="Remove" />
						</a>
					</div>
				</td>
				<td>
					@if ($domain->user->hasExpired ())
						<img src="/img/icons/vhost-expired.png" alt="[Expired]" />
					@endif
					{{ $domain->domain }}
				</td>
				<td>
					<span class="{{ $domain->user->gid < Group::where ('name', 'user')->firstOrFail ()->gid ? 'label' : '' }}">{{ $domain->user->userInfo->username }}</span>
				</td>
			</tr>
			@endforeach
		</tbody>
	</table>
	{{ $domains->links () }}
</fieldset>

@include ('staff.mail.search_part')
@endsection
