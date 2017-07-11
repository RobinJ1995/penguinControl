@extends ('layout.master')

@section ('pageTitle')
FTP accounts
@endsection

@section ('content')
<table>
	<thead>
		<tr>
			<th></th>
			<th>Username</th>
			<th>Directory</th>
			@if (is_admin ())
				<th>User</th>
			@endif
		</tr>
	</thead>
	<tbody>
		@foreach ($ftps as $ftp)
			<tr class="{{ is_owner ($ftp) ? 'owned' : 'notOwned' }}">
			<td>
				@if (! $ftp->locked || is_admin ())
				<div class="button-group radius">
					<a href="/ftp/{{ $ftp->id }}/edit" title="Edit" class="button tiny">
						<img src="/img/icons/edit.png" alt="Edit" />
					</a><a href="/ftp/{{ $ftp->id }}/remove" title="Remove" class="button tiny alert remove">
						<img src="/img/icons/remove.png" alt="Remove" />
					</a>
				</div>
				@endif
			</td>
			<td>
				@if (is_admin ())
					@if ($ftp->locked)
						<img src="/img/icons/locked.png" alt="[Locked]" />
					@endif
					@if ($ftp->user->hasExpired ())
						<img src="/img/icons/vhost-expired.png" alt="[Expired]" />
					@endif
				@endif
				{{ $ftp->username }}
			</td>
			<td>~/{{ substr ($ftp->dir, strlen ($ftp->user->homedir) + 1) }}</td>
			@if (is_admin ())
				<td>
					{{ $ftp->user->label () }}
				</td>
			@endif
		</tr>
		@endforeach
	</tbody>
</table>
@section ('custom_fields')
@show
<div class="right">
	<a href="/ftp/create" title="Add" class="button radius">
		<img src="/img/icons/add.png" alt="Add" />
	</a>
</div>
@endsection