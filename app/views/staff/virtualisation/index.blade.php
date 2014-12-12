@extends ('layout.master')

@section ('pageTitle')
Virtualisatiecluster &bull; Staff
@endsection

@section ('content')
<ul>
	@foreach ($nodes as $node)
	<li>{{ $node->getName () }}
		<ul>
			@foreach ($node->getVMs () as $vm)
			<li>[{{ $vm->getId () }}] {{ $vm->getName () }}</li>
			@endforeach
		</ul>
	</li>
	@endforeach
</ul>
@endsection