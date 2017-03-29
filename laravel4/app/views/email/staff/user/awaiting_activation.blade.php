@extends ('email.template')

@section ('content')
<table>
	<tr>
		<th>Gebruikersnaam</th>
		<td>{{ $userInfo->username }}</td>
	</tr>
	<tr>
		<th>Naam</th>
		<td>{{ $userInfo->getFullName () }}</td>
	</tr>
	<tr>
		<th>E-mailadres</th>
		<td>{{ $userInfo->email }}</td>
	</tr>
	<tr>
		<th>r-nummer</th>
		<td>{{ $userInfo->schoolnr }}</td>
	</tr>
</table>
@endsection