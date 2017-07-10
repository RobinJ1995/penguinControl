@extends ('layout.master')

@section ('pageTitle')
User limits
@endsection

@section ('content')
<fieldset>
	<legend>Global user limits</legend>
	<table>
		<thead>
			<tr>
				<th></th>
				<th>vHosts</th>
				<th>FTP accounts</th>
				<th>E-mail domains</th>
				<th>E-mail accounts</th>
				<th>Forwarding addresses</th>
				<th>Storage space</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>
					<div class="button-group radius">
						<a href="/staff/user/limit/{{ $global->id }}/edit" title="Edit" class="button tiny">
							<img src="/img/icons/edit.png" alt="Edit" />
						</a>
					</div>
				</td>
				<td>{{ $global->vhost }}</td>
				<td>{{ $global->ftp }}</td>
				<td>{{ $global->mail_domain }}</td>
				<td>{{ $global->mail_user }}</td>
				<td>{{ $global->mail_forward }}</td>
				<td>{{ $global->diskusage }} MB</td>
			</tr>
		</tbody>
	</table>
</fieldset>

<fieldset>
	<legend>Exceptions</legend>
	{{ $limits->links () }}
	<table>
		<thead>
			<tr>
				<th></th>
				<th>
					User
				</th>
				<th>
					<a href="/staff/user/limit/order/vhost">vHosts</a>
				</th>
				<th>
					<a href="/staff/user/limit/order/ftp">FTP accounts</a>
				</th>
				<th>
					<a href="/staff/user/limit/order/mail_domain">E-mail domains</a>
				</th>
				<th>
					<a href="/staff/user/limit/order/mail_user">E-mail accounts</a>
				</th>
				<th>
					<a href="/staff/user/limit/order/mail_forward">Forwarding addresses</a>
				</th>
				<th>
					<a href="/staff/user/limit/order/diskusage">Storage space</a>
				</th>
			</tr>
		</thead>
		<tbody>
			@foreach ($limits as $limit)
			<tr>
				<td>
					<div class="button-group radius">
						<a href="/staff/user/limit/{{ $limit->id }}/edit" title="Edit" class="button tiny">
							<img src="/img/icons/edit.png" alt="Edit" />
						</a><a href="/staff/user/limit/{{ $limit->id }}/remove" title="Remove" class="button tiny alert remove confirm">
							<img src="/img/icons/remove.png" alt="Remove" />
						</a>
					</div>
				</td>
				<td>
					<span class="{{ $limit->user->gid < Group::where ('name', 'user')->firstOrFail ()->gid ? 'label' : '' }}">{{ $limit->user->userInfo->username }}</span>
				</td>
				<td>{{ $limit->vhost }}</td>
				<td>{{ $limit->ftp }}</td>
				<td>{{ $limit->mail_domain }}</td>
				<td>{{ $limit->mail_user }}</td>
				<td>{{ $limit->mail_forward }}</td>
				<td>{{ $limit->diskusage }} MB</td>
			</tr>
			@endforeach
		</tbody>
	</table>
	{{ $limits->links () }}
	<div class="right">
		<a href="/staff/user/limit/create" title="Add" class="button radius">
			<img src="/img/icons/add.png" alt="Add" />
		</a>
	</div>
</fieldset>
@endsection