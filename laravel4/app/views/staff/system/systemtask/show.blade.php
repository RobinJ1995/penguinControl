@extends ('layout.master')

@section ('pageTitle')
Systeemopdrachten &bull; Staff
@endsection

@section ('content')
<h1><?php
switch ($task->type)
{
	case SystemTask::TYPE_APACHE_RELOAD:
		echo 'Webserver opnieuw laden';
		break;
	case SystemTask::TYPE_HOMEDIR_PREPARE:
		echo 'Home directory voorbereiden voor <kbd>' . $data['user'] . '</kbd>';
		break;
	case SystemTask::TYPE_NUKE_EXPIRED_VHOSTS:
		echo 'Websites van vervallen gebruikers uitschakelen';
		break;
	case SystemTask::TYPE_PROBLEM_SOLVER:
		echo 'Veelvoorkomende problemen automatisch proberen op te lossen voor <kbd>User#' . $data['userId'] . '</kbd>';
		break;
	case SystemTask::TYPE_CALCULATE_DISK_USAGE:
		echo 'Herbereken schijfruimtegebruik van gebruikers';
		break;
}

$data = json_decode ($task->data, true);
?></h1>

<ul class="large-block-grid-2 medium-block-grid-1 small-block-grid-1">
	@foreach ($data as $key => $value)
	<li>
		<h2>{{ ucfirst ($key) }}</h2>
		<kbd>{{ $value }}</kbd>
	</li>
	@endforeach
	<li>
		<h2>Exit code</h2>
		<kbd>{{ $task->exitcode }}</kbd>
	</li>
</ul>
@endsection