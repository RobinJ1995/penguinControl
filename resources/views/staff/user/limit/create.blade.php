@extends ('layout.master')

@section ('pageTitle')
Add user limit exception
@endsection

@section ('content')
<form action="/staff/user/limit/create" method="POST" data-abide>
	<fieldset>
		<legend>Add user limit exception</legend>
		<div>
			<label>User:
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
				<label>FTP accounts:
					<input type="number" name="ftp" value="{{ Input::old ('ftp', Ftp::getGlobalLimit ()) }}" min="0" max="25" required />
				</label>
				<small class="error">Invalid input</small>
			</div>
			<div class="large-2 medium-4 small-12 column">
				<label>vHosts:
					<input type="number" name="vhost" value="{{ Input::old ('vhost', Vhost::getGlobalLimit ()) }}" min="0" max="25" required />
				</label>
				<small class="error">Invalid input</small>
			</div>
			<div class="large-2 medium-4 small-12 column">
				<label>E-mail domains:
					<input type="number" name="maildomain" value="{{ Input::old ('maildomain', MailDomain::getGlobalLimit ()) }}" min="0" max="25" required />
				</label>
				<small class="error">Invalid input</small>
			</div>
			<div class="large-2 medium-4 small-12 column">
				<label>E-mail accounts:
					<input type="number" name="mailuser" value="{{ Input::old ('mailuser', MailUser::getGlobalLimit ()) }}" min="0" max="25" required />
				</label>
				<small class="error">Invalid input</small>
			</div>
			<div class="large-2 medium-4 small-12 column">
				<label>Forwarding addresses:
					<input type="number" name="mailforward" value="{{ Input::old ('mailforward', MailForward::getGlobalLimit ()) }}" min="0" max="25" required />
				</label>
				<small class="error">Invalid input</small>
			</div>
			<div class="large-2 medium-4 small-12 column">
				<div class="row collapse">
					<label>Storage space</label>
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
			<button name="save" value="{{ time () }}">Save</button>
		</div>
	</fieldset>
</form>
@endsection