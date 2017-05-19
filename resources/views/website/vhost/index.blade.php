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
					<a href="/website/vhost/{{ $vhost->id }}/edit" title="Edit" class="button tiny">
						<img src="/img/icons/edit.png" alt="Edit" />
					</a><a href="/website/vhost/{{ $vhost->id }}/remove" title="Remove" class="button tiny alert remove">
						<img src="/img/icons/remove.png" alt="Remove" />
					</a>
				</div>
				@endif
			</td>
			<td>
				{{ $vhost->servername }}
				@if ($vhost->serveralias)
					<br />
					<span class="serveralias">{{ str_replace (' ', ', ', $vhost->serveralias) }}</span>
				@endif
			</td>
			<td>{{ substr ($vhost->docroot, 0, strlen ($user->homedir)) == $user->homedir ? '~' . substr ($vhost->docroot, strlen ($user->homedir)) : $vhost->docroot }}</td>
			<td>
				@if ($vhost->ssl == 0)
					<span class="label alert">HTTP</span>
				@elseif ($vhost->ssl == 1)
					<span class="label warning">HTTP + HTTPS</span>
				@elseif ($vhost->ssl == 1)
					<span class="label success">HTTP + Redirect</span>
				@endif
			</td>
			<td>
				<span class="label {{ $vhost->cgi ? 'success' : 'alert' }}">{{ $vhost->cgi ? 'Yes' : 'No' }}</span>
			</td>
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