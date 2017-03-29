@extends ('layout.master')

@section ('pageTitle')
Misbruik &bull; Staff
@endsection

@section ('content')
<form action="/staff/user/abuse/multi" method="POST">
	<table>
		<thead>
			<tr>
				<th></th>
				<th>Bestand</th>
				<th>Grootte</th>
				<th>Gebruiker (kapoet)</th>
				<th>Datum</th>
				<th>Status</th>
			</tr>
		</thead>
		<tbody>
			@foreach ($abuses as $abuse)
			<tr>
				<td>
					<input type="checkbox" name="abuses[]" value="{{ htmlentities ($abuse->file) }}" {{ $abuse->status > 0 ? 'class="disabled" disabled' : '' }} />
				</td>
				<td>{{ $abuse->file }}</td>
				<td>{{ $abuse->filesize }}</td>
				<td>{{ $abuse->uid }}</td>
				<td>{{ $abuse->date }}</td>
				<td>
					<span class="label {{ $abuse->status > 0 ? 'success' : ($abuse->status < 0 ? 'alert' : 'warning') }}">{{ $abuse->status > 0 ? 'OK' : ($abuse->status < 0 ? 'Misbruik' : 'Onbekend') }}</span>
				</td>
			</tr>
			@endforeach
		</tbody>
	</table>
	{{ Form::token () }}
	<button name="action" value="whitelist" class="success">Whitelist</button>
	<button name="action" value="blacklist" class="alert">Blacklist</button>
</form>
@endsection