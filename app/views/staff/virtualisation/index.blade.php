@extends ('layout.master')

@section ('pageTitle')
Virtualisatiecluster &bull; Staff
@endsection

@section ('content')
<ul>
	@foreach ($nodes as $node)
	<li>{{ $node->getName () }}</li>
	@endforeach
</ul>
@endsection