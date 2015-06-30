@extends ('layout.master')

@section ('pageTitle')
E-mailaccounts
@endsection

@section ('content')
<table>
	<thead>
		<tr>
			<th></th>
			<th>E-mailadres</th>
		</tr>
	</thead>
	<tbody>
		@foreach ($mUsers as $mUser)
		<tr>
			<td>
				<div class="button-group radius">
					@if($mUser->uid === $mUser->mailDomainVirtual->uid)
					<a href="/mail/user/{{ $mUser->id }}/edit" title="Bewerken" class="button tiny">
						<img src="/img/icons/edit.png" alt="Bewerken" />
					</a><a href="/mail/user/{{ $mUser->id }}/remove" title="Verwijderen" class="button tiny alert remove">
						<img src="/img/icons/remove.png" alt="Verwijderen" />
					</a>
					@endif
				</div>
			</td>
			<td>
				@if($mUser->mailDomainVirtual)
					{{ $mUser->email . '@' . $mUser->mailDomainVirtual->domain }}
				@else
					{{ $mUser->email }} 
				@endif
			</td>
		</tr>
		@endforeach
	</tbody>
</table>
<div class="right">
	<a href="/mail/user/create" title="Toevoegen" class="button radius">
		<img src="/img/icons/add.png" alt="Toevoegen" />
	</a>
</div>
@endsection