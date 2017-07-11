@extends ('layout.master')

@section ('pageTitle')
E-mail accounts
@endsection

@section ('content')
<table>
	<thead>
		<tr>
			<th></th>
			<th>E-mail address</th>
		</tr>
	</thead>
	<tbody>
		@foreach ($mUsers as $mUser)
		<tr>
			<td>
				<div class="button-group radius">
					@if($mUser->uid === $mUser->mailDomain->uid)
					<a href="/mail/user/{{ $mUser->id }}/edit" title="Edit" class="button tiny">
						<img src="/img/icons/edit.png" alt="Edit" />
					</a><a href="/mail/user/{{ $mUser->id }}/remove" title="Remove" class="button tiny alert remove">
						<img src="/img/icons/remove.png" alt="Remove" />
					</a>
					@endif
				</div>
			</td>
			<td>
				@if($mUser->mailDomain)
					{{ $mUser->email . '@' . $mUser->mailDomain->domain }}
				@else
					{{ $mUser->email }} 
				@endif
			</td>
		</tr>
		@endforeach
	</tbody>
</table>
@section ('custom_fields')
@show
<div class="right">
	<a href="/mail/user/create" title="Add" class="button radius">
		<img src="/img/icons/add.png" alt="Add" />
	</a>
</div>
@endsection