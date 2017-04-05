@extends ('layout.master')

@section ('pageTitle')
System Tasks
@endsection

@section ('content')
<h1>{{ $h1 }}</h1>

<ul class="large-block-grid-2 medium-block-grid-1 small-block-grid-1">
	@foreach ($data as $key => $value)
	<li>
		<h2>{{ ucfirst ($key) }}</h2>
		@if (is_array ($value) || (is_string ($value) && strstr ($value, PHP_EOL)))
			<pre>{{ var_export ($value, true) }}</pre>
		@else
			<kbd>{{ $value }}</kbd>
		@endif
	</li>
	@endforeach
	<li>
		<h2>Exit code</h2>
		<kbd>{{ $task->exitcode }}</kbd>
	</li>
</ul>
@endsection