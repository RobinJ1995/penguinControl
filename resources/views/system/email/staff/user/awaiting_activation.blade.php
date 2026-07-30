@extends ('email.template')

@section ('content')
<table>
	<tr>
		<th>Username</th>
		<td>{{ $userInfo->username }}</td>
	</tr>
	<tr>
		<th>Name</th>
		<td>{{ $userInfo->getFullName () }}</td>
	</tr>
	<tr>
		<th>E-mail address</th>
		<td>{{ $userInfo->email }}</td>
	</tr>
	<tr>
	</tr>
</table>
@endsection