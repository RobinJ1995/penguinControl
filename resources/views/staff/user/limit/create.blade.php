@extends ('layout.master')

@section ('pageTitle')
Uitzondering toevoegen &bull; Staff
@endsection

@section ('content')
<form action="/staff/user/limit/create" method="POST" data-abide>
	<fieldset>
		<legend>Uitzondering toevoegen</legend>
		<div>
			<label>Gebruiker:
				{{ Form::select
				(
					'uid',
					$users,
					Input::old ('uid', $user->uid)
				)
				}}
			</label>
		</div>
		<div class="row">
			<div class="large-2 medium-4 small-12 column">
				<label>FTP-accounts:
					<input type="number" name="ftp" value="{{ Input::old ('ftp', FtpUserVirtual::getGlobalLimit ()) }}" min="0" max="25" required />
				</label>
				<small class="error">Invalid input</small>
			</div>
			<div class="large-2 medium-4 small-12 column">
				<label>vHosts:
					<input type="number" name="vhost" value="{{ Input::old ('vhost', ApacheVhostVirtual::getGlobalLimit ()) }}" min="0" max="25" required />
				</label>
				<small class="error">Invalid input</small>
			</div>
			<div class="large-2 medium-4 small-12 column">
				<label>E-maildomeinen:
					<input type="number" name="maildomain" value="{{ Input::old ('maildomain', MailDomainVirtual::getGlobalLimit ()) }}" min="0" max="25" required />
				</label>
				<small class="error">Invalid input</small>
			</div>
			<div class="large-2 medium-4 small-12 column">
				<label>E-mailaccounts:
					<input type="number" name="mailuser" value="{{ Input::old ('mailuser', MailUserVirtual::getGlobalLimit ()) }}" min="0" max="25" required />
				</label>
				<small class="error">Invalid input</small>
			</div>
			<div class="large-2 medium-4 small-12 column">
				<label>Doorstuuradressen:
					<input type="number" name="mailforwarding" value="{{ Input::old ('mailforwarding', MailForwardingVirtual::getGlobalLimit ()) }}" min="0" max="25" required />
				</label>
				<small class="error">Invalid input</small>
			</div>
			<div class="large-2 medium-4 small-12 column">
				<div class="row collapse">
					<label>Schijfruimte</label>
					<div class="small-8 columns">
						<input type="number" name="diskusage" value="{{ Input::old ('diskusage', UserLimit::getGlobalLimit ('diskusage')) }}" min="10" max="500000" required />
						<small class="error">Invalid input</small>
					</div>
					<div class="small-4 columns">
						<span class="postfix">MB</span>
					</div>
				</div>
			</div>
		</div>
		<div>
			{{ Form::token () }}
			<button name="save" value="{{ time () }}">Opslaan</button>
		</div>
	</fieldset>
</form>
@endsection