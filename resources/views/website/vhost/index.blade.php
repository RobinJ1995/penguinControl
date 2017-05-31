@extends ('layout.master')

@section ('pageTitle')
vHosts
@endsection

@section ('content')
<p>Virtual hosts (vHosts) are used to host multiple websites on one server. Please make sure you have your domain's DNS records pointing to the right server.</p>
<p>Please note that changes to vHost configuration may not take effect immediately, as the web server configuration needs to be reloaded in order for this to happen. This may take up to a couple of minutes.</p>
<table>
	<thead>
		<tr>
			<th></th>
			<th>Host</th>
			<th>Document root</th>
			<th>Protocol/CGI</th>
			@if (is_admin ())
				<th>User</th>
			@endif
		</tr>
	</thead>
	<tbody>
		@foreach ($vhosts as $vhost)
		<tr class="{{ is_owner ($vhost) ? 'owned' : 'notOwned' }}">
			<td>
				@if (! $vhost->locked || is_admin ())
				<div class="button-group radius">
					<a href="/website/vhost/{{ $vhost->id }}/edit" title="Edit" class="button tiny">
						<img src="/img/icons/edit.png" alt="Edit" />
					</a><a href="/website/vhost/{{ $vhost->id }}/remove" title="Remove" class="button tiny alert remove">
						<img src="/img/icons/remove.png" alt="Remove" />
					</a>
				</div>
				@endif
			</td>
			<td>
				@if (is_admin ())
					@if ($vhost->locked)
						<img src="/img/icons/locked.png" alt="[Locked]" />
					@endif
					@if ($vhost->user->hasExpired ())
						<img src="/img/icons/vhost-expired.png" alt="[Expired]" />
					@endif
				@endif
				<a href="http://{{ $vhost->servername }}" class="servername">{{ $vhost->servername }}</a>
				@if ($vhost->serveralias)
					@foreach (explode (' ', $vhost->serveralias) as $alias)
						<br />
						<a href="http://{{ $alias }}" class="serveralias">{{ $alias }}</a>
					@endforeach
				@endif
			</td>
			<td>{{ substr ($vhost->docroot, 0, strlen ($user->homedir)) == $user->homedir ? '~' . substr ($vhost->docroot, strlen ($user->homedir)) : $vhost->docroot }}</td>
			<td>
				@if ($vhost->ssl == 0)
					<span class="label alert">HTTP</span>
				@elseif ($vhost->ssl == 1)
					<span class="label warning">HTTP + HTTPS</span>
				@elseif ($vhost->ssl == 2)
					<span class="label success">HTTPS + Redirect</span>
				@endif
				<br />
				<span class="label {{ $vhost->cgi ? 'warning' : 'success' }}">CGI {{ $vhost->cgi ? 'enabled' : 'disabled' }}</span>
			</td>
			@if (is_admin ())
				<td>
					{{ $vhost->user->label () }}
				</td>
			@endif
		</tr>
		@endforeach
	</tbody>
</table>
<div class="right">
	<a href="/website/vhost/create" title="Add" class="button radius">
		<img src="/img/icons/add.png" alt="Add" />
	</a>
</div>
@endsection