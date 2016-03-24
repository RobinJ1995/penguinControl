@extends ('layout.master')

@section ('pageTitle')
Systeemopdrachten &bull; Staff
@endsection

@section ('content')
<table>
	<thead>
		<tr>
			<th></th>
			<th>Opdracht</th>
			<th>Start</th>
			<th>Einde</th>
			<th>Laatste keer</th>
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
					<a href="/staff/system/systemtask/{{ $task->id }}/remove" title="Verwijderen" class="button tiny alert remove confirm">
						<img src="/img/icons/remove.png" alt="Verwijderen" />
					</a><!-- // Anders staat er spatie tussen de knoppen //
					-->@if (! empty ($data))<!--
					--><a href="/staff/system/systemtask/{{ $task->id }}/show" title="Weergeven" class="button tiny">
						<img src="/img/icons/show.png" alt="Weergeven" />
					</a>
					@endif
				</div>
			</td>
			<td>
				<?php
				switch ($task->type)
				{
					case 'apache_reload':
						echo 'Webserver opnieuw laden';
						break;
					case 'homedir_prepare':
						echo 'Home directory voorbereiden voor <kbd>' . $data['user'] . '</kbd>';
						break;
					case 'nuke_expired_vhosts':
						echo 'Websites van vervallen gebruikers uitschakelen';
						break;
					case 'problem_solver':
						echo 'Veelvoorkomende problemen automatisch proberen op te lossen voor <kbd>User#' . $data['userId'] . '</kbd>';
						break;
				}
				?>
			</td>
			<td>
				{{ date ('d/m/Y', $task->start) }}<br />
				{{ date ('H:i:s', $task->start) }}
			</td>
			<td>
				{{ ! empty ($task->end) ? date ('d/m/Y', $task->end) .  '<br />' . PHP_EOL . date ('H:i:s', $task->end) : '' }}
			</td>
			<td>
				{{ ! empty ($task->lastRun) ? date ('d/m/Y', $task->lastRun) .  '<br />' . PHP_EOL . date ('H:i:s', $task->lastRun) : '' }}
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
							$alt = 'Zou gestart moeten zijn maar exit code is onbekend. Mogelijk is de opdracht nog aan het uitvoeren of is er iets mis.';
						}
						else if ($task->exitcode == 0)
						{
							$img = 'task-ok';
							$alt = 'Uitgevoerd en beëindigd met exit code 0.';
						}
						else
						{
							$img = 'task-error';
							$alt = 'Uitgevoerd en beëindigd met exit code ' . $task->exitcode . '.';
						}
					}
					else
					{
						if ($task->exitcode === NULL)
						{
							$img = 'task-unknown';
							$alt = 'Staat gepland om opnieuw uitgevoerd te worden. Van de laatste uitvoering is geen exit code bekend. Mogelijk is de opdracht nog aan het uitvoeren of is er iets mis.';
						}
						else if ($task->exitcode == 0)
						{
							$img = 'task-planned-last-ok';
							$alt = 'Staat gepland om opnieuw uitgevoerd te worden. Laatste uitvoering beëindigde met exit code 0.';
						}
						else
						{
							$img = 'task-planned-last-error';
							$alt = 'Staat gepland om opnieuw uitgevoerd te worden. Laatste uitvoering beëindigde met exit code ' . $task->exitcode . '.';
						}
					}
				}
				else
				{
					$img = 'task-planned';
					$alt = 'Staat gepland om uitgevoerd te worden.';
				}
				?>
				<img src="/img/icons/{{ $img }}.png" alt="{{ $alt }}" title="{{ $alt }}" />
			</td>
		</tr>
		@endforeach
	</tbody>
</table>
<div class="right">
	<a href="/staff/system/systemtask/create" title="Toevoegen" class="button radius">
		<img src="/img/icons/add.png" alt="Toevoegen" />
	</a>
</div>
@endsection