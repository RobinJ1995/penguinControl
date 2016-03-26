@extends ('layout.master')

@section ('pageTitle')
Laravel logs &bull; Staff
@endsection

@section ('content')
<h1>laravel.log</h1>
@foreach ($laravel as $title => $trace)
<div class="panel">
	<p>{{ $title }}</p>
	<pre class="log">{{ $trace }}</pre>
</div>
@endforeach
@endsection