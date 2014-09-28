@extends ('layout.master')

@section ('pageTitle')
Uitzondering bewerken &bull; Staff
@endsection

@section ('content')
<form action="/staff/user/limit/{{ $limit->id }}/edit" method="POST" data-abide>
	<fieldset>
		<legend>Uitzondering bewerken</legend>
		<div>
			<label>Gebruiker:
				{{ Form::select
				(
					'uid',
					$users,
					Input::old ('uid', $limit->uid),
					array ('disabled')
				)
				}}
			</label>
		</div>
		<div class="row">
			<div class="large-2 medium-4 small-12 column">
				<label>FTP-accounts:
					<input type="number" name="ftp" value="{{ Input::old ('ftp', $limit->ftp_user_virtual) }}" min="0" max="25" required />
				</label>
				<small class="error">Ongeldige waarde</small>
			</div>
			<div class="large-2 medium-4 small-12 column">
				<label>vHosts:
					<input type="number" name="vhost" value="{{ Input::old ('vhost', $limit->apache_vhost_virtual) }}" min="0" max="25" required />
				</label>
				<small class="error">Ongeldige waarde</small>
			</div>
			<div class="large-2 medium-4 small-12 column">
				<label>E-maildomeinen:
					<input type="number" name="maildomain" value="{{ Input::old ('maildomain', $limit->mail_domain_virtual) }}" min="0" max="25" required />
				</label>
				<small class="error">Ongeldige waarde</small>
			</div>
			<div class="large-2 medium-4 small-12 column">
				<label>E-mailaccounts:
					<input type="number" name="mailuser" value="{{ Input::old ('mailuser', $limit->mail_user_virtual) }}" min="0" max="25" required />
				</label>
				<small class="error">Ongeldige waarde</small>
			</div>
			<div class="large-2 medium-4 small-12 column">
				<label>Doorstuuradressen:
					<input type="number" name="mailforwarding" value="{{ Input::old ('mailforwarding', $limit->mail_forwarding_virtual) }}" min="0" max="25" required />
				</label>
				<small class="error">Ongeldige waarde</small>
			</div>
			<div class="large-2 medium-4 small-12 column">
				<div class="row collapse">
					<label>Schijfruimte</label>
					<div class="small-8 columns">
						<input type="number" name="diskusage" value="{{ Input::old ('diskusage', $limit->diskusage) }}" min="10" max="500000" required />
						<small class="error">Ongeldige waarde</small>
					</div>
					<div class="small-4 columns">
						<span class="postfix">MB</span>
					</div>
				</div>
			</div>
		</div>
		<div>
			{{ Form::token () }}
			<button name="save" value="{{ $limit->id }}">Opslaan</button>
		</div>
	</fieldset>
</form>
@endsection