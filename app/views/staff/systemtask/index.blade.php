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
			<th>Interval</th>
			<th>Status</th>
		</tr>
	</thead>
	<tbody>
		@foreach ($tasks as $task)
		<tr>
			<td>
				<div class="button-group radius">
					<a href="/staff/systemtask/{{ $task->id }}/remove" title="Verwijderen" class="button tiny alert remove confirm">
						<img src="/img/icons/remove.png" alt="Verwijderen" />
					</a>
				</div>
			</td>
			<td>
				<?php
				$data = json_decode ($task->data, true);
				
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
					case 'custom':
						echo 'Commando: <kbd>' . $data['command'] . '</kbd>';
						break;
				}
				?>
			</td>
			<td>
				{{ date ('j-n-Y', $task->start) }}<br />
				{{ date ('g:i:s A', $task->start) }}
			</td>
			<td>
				{{ ! empty ($task->end) ? date ('j-n-Y', $task->end) .  '<br />' . PHP_EOL . date ('g:i:s A', $task->end) : '' }}
			</td>
			<td>
				<?php
				if (! empty ($task->interval))
				{
					$interval = $task->interval;
					
					$secs = floor ($interval % 60);
					$mins = floor (($interval % 3600) / 60);
					$hours = floor (($interval % 86400) / 3600);
					$days = floor (($interval % 2592000) / 86400);
					$weeks = floor (($interval % 41944000) / 2592000);
					
					$str = '';
					
					if (! empty ($weeks))
						$str .= $weeks . ' weken<br />';
					if (! empty ($days))
						$str .= $days . ' dagen<br />';
					if (! empty ($hours))
						$str .= $hours . ' uur<br />';
					if (! empty ($mins))
						$str .= $mins . ' minuten<br />';
					if (! empty ($secs))
						$str .= $secs . ' seconden<br />';
					
					echo $str;
				}
				?>
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
	<a href="/staff/systemtask/create" title="Toevoegen" class="button radius">
		<img src="/img/icons/add.png" alt="Toevoegen" />
	</a>
</div>
@endsection