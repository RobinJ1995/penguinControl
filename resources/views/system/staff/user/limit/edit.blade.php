@extends ('layout.master')

@section ('pageTitle')
Edit user limit exception
@endsection

@section ('content')
<form action="/staff/user/limit/{{ $limit->id }}/edit" method="POST" data-abide>
	<fieldset>
		<legend>Edit user limit exception</legend>
		<div>
			<label>User:
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
				<label>FTP accounts:
					<input type="number" name="ftp" value="{{ Input::old ('ftp', $limit->ftp) }}" min="0" max="25" required />
				</label>
				<small class="error">Invalid input</small>
			</div>
			<div class="large-2 medium-4 small-12 column">
				<label>vHosts:
					<input type="number" name="vhost" value="{{ Input::old ('vhost', $limit->vhost) }}" min="0" max="25" required />
				</label>
				<small class="error">Invalid input</small>
			</div>
			<div class="large-2 medium-4 small-12 column">
				<label>E-mail domains:
					<input type="number" name="maildomain" value="{{ Input::old ('maildomain', $limit->mail_domain) }}" min="0" max="25" required />
				</label>
				<small class="error">Invalid input</small>
			</div>
			<div class="large-2 medium-4 small-12 column">
				<label>E-mail accounts:
					<input type="number" name="mailuser" value="{{ Input::old ('mailuser', $limit->mail_user) }}" min="0" max="25" required />
				</label>
				<small class="error">Invalid input</small>
			</div>
			<div class="large-2 medium-4 small-12 column">
				<label>Forwarding addresses:
					<input type="number" name="mailforward" value="{{ Input::old ('mailforward', $limit->mail_forward) }}" min="0" max="25" required />
				</label>
				<small class="error">Invalid input</small>
			</div>
			<div class="large-2 medium-4 small-12 column">
				<div class="row collapse">
					<label>Storage space</label>
					<div class="small-8 columns">
						<input type="number" name="diskusage" value="{{ Input::old ('diskusage', $limit->diskusage) }}" min="10" max="500000" required />
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
			<button name="save" value="{{ $limit->id }}">Save</button>
		</div>
	</fieldset>
</form>
@endsection