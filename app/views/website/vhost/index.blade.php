@extends ('layout.master')

@section ('pageTitle')
vHosts
@endsection

@section ('content')
<p>Virtual hosts (vHosts) kunnen gebruikt worden om subdomeinen aan te maken. Standaard kan u een subdomein volgens het patroon <em>*.{{ $user->userInfo->username }}.sinners.be</em> aanmaken. Als u een ander domein in uw bezit heeft en de DNS-records voor uw domein juist staan ingesteld kunt u eveneens uw webruimte op SIN koppelen aan uw domein door middel van een vHost. Let op dat wijzigingen aan uw vHosts niet onmiddellijk van toepassing zijn. Hiervoor moet de webserver zijn configuratie opnieuw inlezen, wat volgens de huidige systeeminstellingen elke {{ SystemTask::friendlyInterval ($apacheReloadInterval) }} gebeurt.</p>
<table>
	<thead>
		<tr>
			<th></th>
			<th>Host</th>
			<th>Beheerder</th>
			<th>Alias</th>
			<th>Document root</th>
			<th>Protocol</th>
			<th>CGI</th>
		</tr>
	</thead>
	<tbody>
		@foreach ($vhosts as $vhost)
		<tr>
			<td>
				@if (! $vhost->locked)
				<div class="button-group radius">
					<a href="/website/vhost/{{ $vhost->id }}/edit" title="Bewerken" class="button tiny">
						<img src="/img/icons/edit.png" alt="Bewerken" />
					</a><a href="/website/vhost/{{ $vhost->id }}/remove" title="Verwijderen" class="button tiny alert remove">
						<img src="/img/icons/remove.png" alt="Verwijderen" />
					</a>
				</div>
				@endif
			</td>
			<td>{{ $vhost->servername }}</td>
			<td>{{ $vhost->serveradmin }}</td>
			<td>{{ $vhost->serveralias }}</td>
			<td>{{ substr ($vhost->docroot, 0, strlen ($user->homedir)) == $user->homedir ? '~' . substr ($vhost->docroot, strlen ($user->homedir)) : $vhost->docroot }}</td>
			<td>
				<span class="label {{ $vhost->ssl ? 'success' : 'alert' }}">{{ $vhost->ssl == 0 ? 'HTTP' : ($vhost->ssl == 1 ? 'HTTPS' : 'HTTPS + Redirect') }}</span>
			</td>
			<td>
				<span class="label {{ $vhost->cgi ? 'success' : 'alert' }}">{{ $vhost->cgi ? 'Ja' : 'Nee' }}</span>
			</td>
		</tr>
		@endforeach
	</tbody>
</table>
<div class="right">
	<a href="/website/vhost/create" title="Toevoegen" class="button radius">
		<img src="/img/icons/add.png" alt="Toevoegen" />
	</a>
</div>
@endsection