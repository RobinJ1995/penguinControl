@extends ('layout.master')

@section ('pageTitle')
Start
@endsection

@section ('content')
<div class="large-6 small-12 column">
	<table>
		<caption>Account information</caption>
		<tr>
			<th>Username</th>
			<td>{{ $userInfo->username }}</td>
		</tr>
		<tr>
			<th>E-mail address</th>
			<td>{{ $userInfo->email }}</td>
		</tr>
		<tr>
			<th>Name</th>
			<td>{{ $userInfo->fname }} {{ $userInfo->lname }}</td>
		</tr>
		<tr>
			<th>Primary group</th>
			<td>{{ ucfirst ($user->primaryGroup->name) }}</td>
		</tr>
		<tr>
			<th>Shell</th>
			<td>{{ $user->shell }}</td>
		</tr>
		<tr>
			<th>Home directory</th>
			<td>{{ $user->homedir }}</td>
		</tr>
	</table>
</div>

<div class="large-6 small-12 column">
	<table>
		<caption>Usage</caption>
		<tr>
			<th>Storage</th>
			<td>
				<span class="label {{ ($user->diskusage < $user->getLimit ('diskusage') / 5 ? "success" : ($user->diskusage >= $user->getLimit ('diskusage') ? "alert" : "warning") ) }}">{{ $user->diskusage }} MB</span>
			</td>
			<td></td>
		</tr>
		<tr>
			<th>Websites (vHosts)</th>
			<td>
				<div class="progress">
					<span class="meter" style="width: {{ (\App\Models\Vhost::getCount ($user) / \App\Models\Vhost::getLimit ($user)) * 100 }}%"></span>
				</div>
			</td>
			<td>{{ \App\Models\Vhost::getCount ($user) }}/{{ \App\Models\Vhost::getLimit ($user) }}</td>
		</tr>
		{{--<tr>
			<th>Databases</th>
			<td>
				<div class="progress">
					<span class="meter" style="width: 33%"></span>
				</div>
			</td>
			<td>?</td> <!-- //TODO// -->
		</tr>
		<tr>
			<th>Databasegebruikers</th>
			<td>
				<div class="progress">
					<span class="meter" style="width: 33%"></span>
				</div>
			</td>
			<td>?</td> <!-- //TODO// -->
		</tr>--}}
		<tr>
			<th>FTP accounts</th>
			<td>
				<div class="progress">
					<span class="meter" style="width: {{ (\App\Models\Ftp::getCount ($user) / \App\Models\Ftp::getLimit ($user)) * 100 }}%"></span>
				</div>
			</td>
			<td>{{ \App\Models\Ftp::getCount ($user) }}/{{ \App\Models\Ftp::getLimit ($user) }}</td>
		</tr>
		<tr>
			<th>E-mail domains</th>
			<td>
				<div class="progress">
					<span class="meter" style="width: {{ (\App\Models\MailDomain::getCount ($user) / \App\Models\MailDomain::getLimit ($user)) * 100 }}%"></span>
				</div>
			</td>
			<td>{{ \App\Models\MailDomain::getCount ($user) }}/{{ \App\Models\MailDomain::getLimit ($user) }}</td>
		</tr>
		<tr>
			<th>E-mail accounts</th>
			<td>
				<div class="progress">
					<span class="meter" style="width: {{ (\App\Models\MailUser::getCount ($user) / \App\Models\MailUser::getLimit ($user)) * 100 }}%"></span>
				</div>
			</td>
			<td>{{ \App\Models\MailUser::getCount ($user) }}/{{ \App\Models\MailUser::getLimit ($user) }}</td>
		</tr>
		<tr>
			<th>Forwarding addresses</th>
			<td>
				<div class="progress">
					<span class="meter" style="width: {{ (\App\Models\MailForward::getCount ($user) / \App\Models\MailForward::getLimit ($user)) * 100 }}%"></span>
				</div>
			</td>
			<td>{{ \App\Models\MailForward::getCount ($user) }}/{{ \App\Models\MailForward::getLimit ($user) }}</td>
		</tr>
	</table>
</div>
@endsection