@extends ('layout.master')

@section ('css')
@parent
<link rel="stylesheet" media="print" href="/css/print.css" />
@endsection

@section ('pageTitle')
Facturatie &bull; Staff
@endsection

@section ('js')
@parent
<script type="text/javascript">
	$(document).ready(function () {
		
		$('#selectAllUserLog').change(function () {
			$('input[name="userLogId[]"]').prop('checked', $(this).prop("checked"));
		});

		$('input[name="userLogId[]"]').change(function () {
			$('#selectAllUserLog').prop('checked', false);
		});
		
	});
</script>
@endsection

@section ('content')
<p>{{ $count }} zoekresultaten</p>

{{ $paginationOn ? $userlogs->appends (Input::all ())->links () : '' }}
<form id="log" action="/staff/user/log/edit/checked" method="post">
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
				<th>
					<input type="checkbox" id="selectAllUserLog" value="true" />
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
				<td><img src="/img/icons/{{ $userlog->nieuw ? 'validate' : 'reject' }}.png" alt="" /></td>
				<td>{{ $boekhoudingBetekenis[$userlog->boekhouding]}}</td>
				<td>
					@if (! empty ($userlog->user_info->user))
					<span class="{{ $userlog->user_info->user->gid < Group::where ('name', 'user')->firstOrFail ()->gid ? 'label' : '' }}">{{ ucfirst ($userlog->user_info->user->primaryGroup->name) }}</span>
					@endif
				</td>
				<td>
					<input type="checkbox" name="userLogId[]" value="{{ $userlog->id }}">
				</td>
			</tr>
			@endforeach
		</tbody>
	</table>
	{{ $paginationOn ? $userlogs->appends (Input::all ())->links () : '' }}

	<div class="right">
		<label>
			{{
				Form::select
				(
					'boekhouding',
					$boekhoudingBetekenis,
					0
				)
			}}
		</label>
		{{ csrf_field () }}
		<input type="submit" name="facturatie" value="Change billing status" class="button"/>
		<input type="submit" name="export" value="Export" class="button"/>
	</div>
</form>

@include ('staff.user.log.part.search')
@endsection