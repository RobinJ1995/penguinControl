@extends ('layout.master')

@section ('css')
@parent
<link rel="stylesheet" media="print" href="/css/print.css" />
@endsection

@section ('pageTitle')
Billing
@endsection

@section ('content')
{{ $userlogs->links () }}
<table>
	<thead>
		<tr>
			<th></th>
			<th>
				Username
			</th>
			<th>
				Date/Time
			</th>
			<th>
				New
			</th>
			<th>
				Status
			</th>
			<th>
				Primary group
			</th>
		</tr>
	</thead>
	<tbody>
		@foreach ($userlogs as $userlog)
		<tr>
			<td>
				<div class="button-group radius">
					<a href="/staff/user/log/{{ $userlog->id }}/edit" title="Edit" class="button tiny">
						<img src="/img/icons/edit.png" alt="Edit" />
					</a><a href="/staff/user/log/{{ $userlog->id }}/remove" title="Remove" class="button tiny alert remove confirm">
						<img src="/img/icons/remove.png" alt="Remove" />
					</a>
				</div>
			</td>
			<td>{{ $userlog->userInfo->username }}</td>
			<td>{{ $userlog->time }}</td>
			<td><img src="/img/icons/{{ $userlog->new ? 'validate.png' : 'reject.png' }}" alt="" /></td>
			<td>{{ $statusMeaning[$userlog->status] }}</td>
			<td>
				@if (! empty ($userlog->user))
					<span class="{{ $userlog->user->gid < Group::where ('name', 'user')->firstOrFail ()->gid ? 'label' : '' }}">{{ ucfirst ($userlog->user->primaryGroup->name) }}</span>
				@endif
			</td>
		</tr>
		@endforeach
	</tbody>
</table>
{{ $userlogs->links () }}
<div class="right">
	<a href="/staff/user/log/create" title="Add entry" class="button radius">
		<img src="/img/icons/add.png" alt="Add entry" />
	</a>
</div>

@include ('staff.user.log.part.search')
@endsection
