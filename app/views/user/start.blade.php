@extends ('layout.master')

@section ('pageTitle')
Start
@endsection

@section ('content')
<div class="large-6 small-12 column">
	<table>
		<caption>Gebruikersgegevens</caption>
		<tr>
			<th>Gebruikersnaam</th>
			<td>{{ $userInfo->username }}</td>
		</tr>
		<tr>
			<th>E-mailadres</th>
			<td>{{ $userInfo->email }}</td>
		</tr>
		<tr>
			<th>Studentnummer</th>
			<td>{{ $userInfo->schoolnr }}</td>
		</tr>
		<tr>
			<th>Naam</th>
			<td>{{ $userInfo->fname }} {{ $userInfo->lname }}</td>
		</tr>
		<tr>
			<th>Groep</th>
			<td>{{ ucfirst (Group::where ('gid', $user->gid)->get ()[0]->name) }}</td>
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
		<caption>Verbruik</caption>
		<tr>
			<th>Schijfruimte</th>
			<td>
				<span class="label {{ ($user->diskusage < UserLimit::getLimit ($user, 'diskusage') / 5 ? "success" : ($user->diskusage >= UserLimit::getLimit ($user, 'diskusage') ? "alert" : "warning") ) }}">{{ $user->diskusage }} MB</span>
			</td>
			<td></td>
		</tr>
		<tr>
			<th>Websites (vHosts)</th>
			<td>
				<div class="progress">
					<span class="meter" style="width: {{ (ApacheVhostVirtual::getCount ($user) / ApacheVhostVirtual::getLimit ($user)) * 100 }}%"></span>
				</div>
			</td>
			<td>{{ ApacheVhostVirtual::getCount ($user) }}/{{ ApacheVhostVirtual::getLimit ($user) }}</td>
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
			<th>FTP-gebruikers</th>
			<td>
				<div class="progress">
					<span class="meter" style="width: {{ (FtpUserVirtual::getCount ($user) / FtpUserVirtual::getLimit ($user)) * 100 }}%"></span>
				</div>
			</td>
			<td>{{ FtpUserVirtual::getCount ($user) }}/{{ FtpUserVirtual::getLimit ($user) }}</td>
		</tr>
		<tr>
			<th>E-maildomeinen</th>
			<td>
				<div class="progress">
					<span class="meter" style="width: {{ (MailDomainVirtual::getCount ($user) / MailDomainVirtual::getLimit ($user)) * 100 }}%"></span>
				</div>
			</td>
			<td>{{ MailDomainVirtual::getCount ($user) }}/{{ MailDomainVirtual::getLimit ($user) }}</td>
		</tr>
		<tr>
			<th>E-mailadressen</th>
			<td>
				<div class="progress">
					<span class="meter" style="width: {{ (MailUserVirtual::getCount ($user) / MailUserVirtual::getLimit ($user)) * 100 }}%"></span>
				</div>
			</td>
			<td>{{ MailUserVirtual::getCount ($user) }}/{{ MailUserVirtual::getLimit ($user) }}</td>
		</tr>
		<tr>
			<th>Doorstuuradressen</th>
			<td>
				<div class="progress">
					<span class="meter" style="width: {{ (MailForwardingVirtual::getCount ($user) / MailForwardingVirtual::getLimit ($user)) * 100 }}%"></span>
				</div>
			</td>
			<td>{{ MailForwardingVirtual::getCount ($user) }}/{{ MailForwardingVirtual::getLimit ($user) }}</td>
		</tr>
	</table>
</div>
@endsection