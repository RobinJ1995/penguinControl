@extends ('layout.master')

@section ('pageTitle')
System tasks &bull; Staff
@endsection

@section ('content')
<table>
	<thead>
		<tr>
			<th></th>
			<th>Task</th>
			<th>Start</th>
			<th>End</th>
			<th>Last run</th>
			<th>Interval</th>
			<th>Status</th>
		</tr>
	</thead>
	<tbody>
		@foreach ($tasks as $task)
		<?php
			$data = json_decode ($task->data, true);
		?>
		<tr>
			<td>
				<div class="button-group radius">
					<a href="/staff/system/systemtask/{{ $task->id }}/remove" title="Remove" class="button tiny alert remove confirm">
						<img src="/img/icons/remove.png" alt="Remove" />
					</a><!-- // Otherwise there is whitespace between the buttons //
					-->@if (! empty ($data))<!--
					--><a href="/staff/system/systemtask/{{ $task->id }}/show" title="Show" class="button tiny">
						<img src="/img/icons/show.png" alt="Show" />
					</a>
					@endif
				</div>
			</td>
			<td>
				{!! $task->getTitle () !!}
			</td>
			<td>
				@if (! empty ($task->start))
				{{ date ('d/m/Y', $task->start) }}<br />
				{{ date ('H:i:s', $task->start) }}
				@endif
			</td>
			<td>
				@if (! empty ($task->end))
				{{ date ('d/m/Y', $task->end) }}<br />
				{{ date ('H:i:s', $task->end) }}
				@endif
			</td>
			<td>
				@if (! empty ($task->lastRun))
				{{ date ('d/m/Y', $task->lastRun) }}<br />
				{{ date ('H:i:s', $task->lastRun) }}
				@endif
			</td>
			<td>
				@if (! empty ($task->interval))
				{{ $task->interval () }}
				@endif
			</td>
			<td>
				<?php
				$img = '';
				$alt = '';
				
				$now = time ();
				
				/*
				$now >= $start, empty interval, exitcode 0:	task-ok
				$now >= $start, empty interval, empty exitcode:	task-unknown
				$now >= $start, empty interval, exitcode != 0:	task-error

				$now >= $start, interval, exitcode 0:		task-planned-last-ok
				$now >= $start, interval, empty exitcode:	task-unknown
				$now >= $start, interval, exitcode != 0:		task-planned-last-error

				$now < $start:					task-planned
				*/
				
				if ($now >= $task->start)
				{
					if (empty ($task->interval))
					{
						if ($task->exitcode === NULL)
						{
							$img = 'task-unknown';
							$alt = 'Should have started but the exit code is unknown. The task may still be running, or something went wrong.';
						}
						else if ($task->exitcode == 0)
						{
							$img = 'task-ok';
							$alt = 'Executed and finished with exit code 0.';
						}
						else
						{
							$img = 'task-error';
							$alt = 'Executed and finished with exit code ' . $task->exitcode . '.';
						}
					}
					else
					{
						if ($task->exitcode === NULL)
						{
							$img = 'task-unknown';
							$alt = 'Scheduled to run again. No exit code is known for the last run. The task may still be running, or something went wrong.';
						}
						else if ($task->exitcode == 0)
						{
							$img = 'task-planned-last-ok';
							$alt = 'Scheduled to run again. The last run finished with exit code 0.';
						}
						else
						{
							$img = 'task-planned-last-error';
							$alt = 'Scheduled to run again. The last run finished with exit code ' . $task->exitcode . '.';
						}
					}
				}
				else
				{
					$img = 'task-planned';
					$alt = 'Scheduled to run.';
				}
				?>
				<img src="/img/icons/{{ $img }}.png" alt="{{ $alt }}" title="{{ $alt }}" />
			</td>
		</tr>
		@endforeach
	</tbody>
</table>
<div class="right">
	<a href="/staff/system/systemtask/create" title="Add" class="button radius">
		<img src="/img/icons/add.png" alt="Add" />
	</a>
</div>
@endsection