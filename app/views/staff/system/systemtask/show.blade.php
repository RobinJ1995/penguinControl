@extends ('layout.master')

@section ('pageTitle')
Systeemopdrachten &bull; Staff
@endsection

@section ('content')
<h1><?php
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