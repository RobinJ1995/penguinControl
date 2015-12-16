@extends ('layout.master')

@section ('pageTitle')
Logs &bull; Staff
@endsection

@section ('content')
<table>
	<thead>
		<tr>
			<th></th>
			<th>Datum/Tijd</th>
			<th>Gebruiker</th>
			<th>Gebeurtenis</th>
		</tr>
	</thead>
	<tbody>
		@foreach ($logs as $log)
		<?php
			$data = json_decode ($log->data, true);
		?>
		<tr>
			<td>
				<div class="button-group radius">
					<a href="/staff/system/log/{{ $log->id }}/show" title="Weergeven" class="button tiny">
						<img src="/img/icons/show.png" alt="Weergeven" />
					</a>
				</div>
			</td>
			<td>
				{{ $log->created_at }}
			</td>
			<td>
				@if ($log->user != NULL)
				<span class="{{ $log->user->gid < Group::where ('name', 'user')->firstOrFail ()->gid ? 'label' : '' }}">{{ $log->user->userInfo->username }}</span>
				@endif
			</td>
			<td>
				{{ $log->message }}
			</td>
		</tr>
		@endforeach
	</tbody>
</table>

{{ $logs->links () }}
@endsection