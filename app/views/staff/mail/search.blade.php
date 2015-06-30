@extends ('layout.master')

@section ('pageTitle')
E-maildomeinen en -adressen &bull; Staff
@endsection

@section ('content')
{{ $mUsers->links () }}
<fieldset>
	<legend>{{ $mUsersCount }} e-mailaccounts gevonden</legend>
	
	<table>
		<thead>
			<tr>
				<th></th>
				<th>E-mailadres</th>
				<th>Gebruiker</th>
			</tr>
		</thead>
		<tbody>
			@foreach ($mUsers as $mUser)
			<tr>
				<td>
					<div class="button-group radius">
						<a href="/staff/mail/user/{{ $mUser->id }}/edit" title="Bewerken" class="button tiny">
							<img src="/img/icons/edit.png" alt="Bewerken" />
						</a><a href="/staff/mail/user/{{ $mUser->id }}/remove" title="Verwijderen" class="button tiny alert remove">
							<img src="/img/icons/remove.png" alt="Verwijderen" />
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
	<legend>{{ $mFwdsCount }} doorstuuradressen gevonden</legend>
	
	{{ $mFwds->links () }}
	<table>
		<thead>
			<tr>
				<th></th>
				<th>E-mailadres</th>
				<th>Bestemming</th>
				<th>Gebruiker</th>
			</tr>
		</thead>
		<tbody>
			@foreach ($mFwds as $mFwd)
			<tr>
				<td>
					<div class="button-group radius">
						<a href="/staff/mail/forwarding/{{ $mFwd->id }}/edit" title="Bewerken" class="button tiny">
							<img src="/img/icons/edit.png" alt="Bewerken" />
						</a><a href="/staff/mail/forwarding/{{ $mFwd->id }}/remove" title="Verwijderen" class="button tiny alert remove">
							<img src="/img/icons/remove.png" alt="Verwijderen" />
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
	<legend>{{ $domainsCount }} e-maildomeinen gevonden</legend>
	
	{{ $domains->links () }}
	<table>
		<thead>
			<tr>
				<th></th>
				<th>Domein</th>
				<th>Gebruiker</th>
			</tr>
		</thead>
		<tbody>
			@foreach ($domains as $domain)
			<tr>
				<td>
					<div class="button-group radius">
						<a href="/staff/mail/domain/{{ $domain->id }}/edit" title="Bewerken" class="button tiny">
							<img src="/img/icons/edit.png" alt="Bewerken" />
						</a><a href="/staff/mail/domain/{{ $domain->id }}/remove" title="Verwijderen" class="button tiny alert remove">
							<img src="/img/icons/remove.png" alt="Verwijderen" />
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
