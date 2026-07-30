@extends ('layout.master')

@section ('pageTitle')
Problem solver (overview)
@endsection

@section ('content')
@foreach ($data as $username => $results)
@if (! empty ($results))
<div class="panel">
	<h2>{{ $username }}</h2>
	
	<table>
		<tr>
			<th>Problem</th>
			<th>Component</th>
		</tr>
		@foreach ($results as $result)
		<tr>
			<td>{{ $result['message'] }}</td>
			<td>{{ $result['object'] }}</td>
		</tr>
		@endforeach
	</table>
</div>
@endif
@endforeach
@endsection
