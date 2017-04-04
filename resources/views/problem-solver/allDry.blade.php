@extends ('layout.master')

@section ('pageTitle')
Probleemoplosser (overzicht)
@endsection

@section ('content')
@foreach ($data as $username => $results)
@if (! empty ($results))
<div class="panel">
	<h2>{{ $username }}</h2>
	
	<table>
		<tr>
			<th>Probleem</th>
			<th>Onderdeel</th>
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
