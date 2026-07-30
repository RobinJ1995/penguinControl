@extends ('layout.master')

@section ('pageTitle')
Edit user
@endsection

@section ('content')
<form action="/staff/user/user/{{ $user->id }}/edit" method="POST" data-abide>
	<fieldset>
		<legend>Edit user</legend>
		<div class="row">
			<div class="large-2 medium-3 small-12 column">
				<label>UID:
					<input type="number" name="uid" value="{{ $user->uid }}" min="{{ $user->uid }}" max="{{ $user->uid }}" disabled />
				</label>
				<small class="error">Hands off!</small>
			</div>
			<div class="large-4 medium-9 small-12 column">
				<label>Username:
					<input type="text" name="username" value="{{ $userInfo->username }}" disabled />
				</label>
				<small class="error">Hands off!</small>
			</div>
			<div class="large-6 medium-12 small-12 column">
				<label>Home directory:
					<input type="text" name="homedir" value="{{ $user->homedir }}" disabled />
				</label>
				<small class="error">Hands off!</small>
			</div>
		</div>
		<div class="row">
			<div class="large-4 medium-12 small-12 column">
				<label>E-mail address:
					<input type="email" name="email" value="{{ $userInfo->email }}" required />
				</label>
				<small class="error">Invalid input</small>
			</div>
			<div class="large-4 medium-6 small-12 column">
				<label>First name:
					<input type="text" name="fname" value="{{ $userInfo->fname }}" required />
				</label>
				<small class="error">Invalid input</small>
			</div>
			<div class="large-4 medium-6 small-12 column">
				<label>Surname:
					<input type="text" name="lname" value="{{ $userInfo->lname }}" required />
				</label>
				<small class="error">Invalid input</small>
			</div>
		</div>
		<div class="row">
			<div class="large-4 medium-4 small-12 column">
				<label>Student number:
					<input type="text" name="rnummer" value="{{ $userInfo->schoolnr }}" />
				</label>
				<small class="error">Invalid input</small>
			</div>
			<div class="large-4 medium-4 small-12 column">
				<label>Shell:
					{{ Form::select
						(
							'shell',
							allowed_shells (),
							old ('shell', $user->shell)
						)
					}}
				</label>
				<small class="error">Invalid input</small>
			</div>
			<div class="large-4 medium-4 small-12 column">
				<label>E-mail:
					{{ Form::select
						(
							'mailEnabled',
							array
							(
								'0' => 'Disabled',
								'1' => 'Enabled',
								'-1' => 'Blocked'
							),
							old ('mailEnabled', $user->mail_enabled)
						)
					}}
				</label>
				<small class="error">Invalid input</small>
			</div>
		</div>
		<div class="row">
			<div class="large-6 medium-6 small-12 column">
				<label>Password:
					<input type="password" name="password" id="newPass" />
				</label>
				<small class="error">Invalid input</small>
			</div>
			<div class="large-6 medium-6 small-12 column">
				<label>Password (confirmation):
					<input type="password" name="password_confirm" data-equalto="newPass" />
				</label>
				<small class="error">Invalid input</small>
			</div>
		</div>
		<div class="row">
			<div class="large-12 column">
			<fieldset>
				<legend>Group</legend>
				<table>
					<thead>
						<tr>
							<th>Primary</th>
							<th>Member</th>
							<th>Group</th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						@foreach ($groups as $group)
						<tr>
							<td>
								<input type="radio" name="groupPrimary" value="{{ $group->gid }}" {{ $user->gid == $group->gid ? 'checked' : '' }} />
							</td>
							<td>
								<input type="checkbox" name="groups[]" value="{{ $group->gid }}" {{ $user->isGroupMember ($group) ? 'checked' : '' }} {{ $user->gid == $group->gid ? 'disabled' : '' }} />
							</td>
							<td>
								{{ ucfirst ($group->name) }}
							</td>
							<td>
								<img src="/img/icons/{{ $group->gid < Group::where ('name', 'user')->firstOrFail ()->gid ? 'sin.png" alt="Administrator" title="Administrator' : 'user.png" alt="User' }}" />
							</td>
						</tr>
						@endforeach
					</tbody>
				</table>
			</fieldset>
			</div>
		</div>
		<div>
			{{ Form::token () }}
			<button name="save" value="{{ $user->id }}">Save</button>
		</div>
	</fieldset>
</form>
@endsection
