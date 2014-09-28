@extends ('layout.master')

@section ('pageTitle')
Gebruikerslimieten &bull; Staff
@endsection

@section ('content')
<fieldset>
	<legend>Globale gebruikerslimieten</legend>
	<table>
		<thead>
			<tr>
				<th></th>
				<th>FTP-accounts</th>
				<th>vHosts</th>
				<th>E-maildomeinen</th>
				<th>E-mailaccounts</th>
				<th>Doorstuuradressen</th>
				<th>Schijfruimte</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>
					<div class="button-group radius">
						<a href="/staff/user/limit/{{ $global->id }}/edit" title="Bewerken" class="button tiny">
							<img src="/img/icons/edit.png" alt="Bewerken" />
						</a>
					</div>
				</td>
				<td>{{ $global->ftp_user_virtual }}</td>
				<td>{{ $global->apache_vhost_virtual }}</td>
				<td>{{ $global->mail_domain_virtual }}</td>
				<td>{{ $global->mail_user_virtual }}</td>
				<td>{{ $global->mail_forwarding_virtual }}</td>
				<td>{{ $global->diskusage }} MB</td>
			</tr>
		</tbody>
	</table>
</fieldset>

<fieldset>
	<legend>Uitzonderingen</legend>
	<table>
		<thead>
			<tr>
				<th></th>
				<th>
					<a href="{{ $url }}/order/username">Gebruiker</a>
				</th>
				<th>
					<a href="{{ $url }}/order/ftp_user_virtual">FTP-accounts</a>
				</th>
				<th>
					<a href="{{ $url }}/order/apache_vhost_virtual">vHosts</a>
				</th>
				<th>
					<a href="{{ $url }}/order/mail_domain_virtual">E-maildomeinen</a>
				</th>
				<th>
					<a href="{{ $url }}/order/mail_user_virtual">E-mailaccounts</a>
				</th>
				<th>
					<a href="{{ $url }}/order/mail_forwarding_virtual">Doorstuuradressen</a>
				</th>
				<th>
					<a href="{{ $url }}/order/user_limit.diskusage">Schijfruimte</a>
				</th>
			</tr>
		</thead>
		<tbody>
			@foreach ($limits as $limit)
			<tr>
				<td>
					<div class="button-group radius">
						<a href="/staff/user/limit/{{ $limit->id }}/edit" title="Bewerken" class="button tiny">
							<img src="/img/icons/edit.png" alt="Bewerken" />
						</a><a href="/staff/user/limit/{{ $limit->id }}/remove" title="Verwijderen" class="button tiny alert remove confirm">
							<img src="/img/icons/remove.png" alt="Verwijderen" />
						</a>
					</div>
				</td>
				<td>
					<span class="{{ $limit->getUser ()->gid < Group::where ('name', 'user')->firstOrFail ()->gid ? 'label' : '' }}">{{ $limit->getUser ()->getUserInfo ()->username }}</span>
				</td>
				<td>{{ $limit->ftp_user_virtual }}</td>
				<td>{{ $limit->apache_vhost_virtual }}</td>
				<td>{{ $limit->mail_domain_virtual }}</td>
				<td>{{ $limit->mail_user_virtual }}</td>
				<td>{{ $limit->mail_forwarding_virtual }}</td>
				<td>{{ $limit->diskusage }} MB</td>
			</tr>
			@endforeach
		</tbody>
	</table>
	<div class="right">
		<a href="/staff/user/limit/create" title="Toevoegen" class="button radius">
			<img src="/img/icons/add.png" alt="Toevoegen" />
		</a>
	</div>
</fieldset>
@endsection