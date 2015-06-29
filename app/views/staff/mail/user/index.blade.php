@extends ('layout.master')

@section ('pageTitle')
E-mailaccounts &bull; Staff
@endsection

@section ('content')
{{ $mUsers->links () }}
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
				@if($mUser->mailDomainVirtual)
					{{ $mUser->email. '@' . $mUser->mailDomainVirtual->domain }}
				@else
					{{ $mUser->email }} 
				@endif
			</td>
			<td>
				<span class="{{ $mUser->getUser ()->gid < Group::where ('name', 'user')->firstOrFail ()->gid ? 'label' : '' }}">{{ $mUser->getUser ()->userInfo->username }}</span>
			</td>
		</tr>
		@endforeach
	</tbody>
</table>
{{ $mUsers->links () }}
<div class="right">
	<a href="/staff/mail/user/create" title="Toevoegen" class="button radius">
		<img src="/img/icons/add.png" alt="Toevoegen" />
	</a>
</div>

@include ('staff.mail.search_part')
@endsection
