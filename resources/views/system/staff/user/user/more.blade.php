@extends ('layout.master')

@section ('pageTitle')
More user options &bull; Staff
@endsection

@section ('content')
<ul class="button-group even-2 staffUserMore">
	<li>
		<a href="/staff/user/user/{{ $user->id }}/login" title="Log in as user" class="button">
			<img src="/img/icons/login.png" alt="" /> Log in as {{ $userInfo->username }}
		</a>
	</li>
	<li>
		<a href="/staff/user/user/{{ $user->id }}/expire" title="Change expiry date" class="button">
			<img src="/img/icons/expire.png" alt="" /> Change expiry date
		</a>
	</li>
	<li>
		<a href="/staff/user/user/{{ $user->id }}/edit" title="Edit" class="button">
			<img src="/img/icons/edit.png" alt="" /> Edit
		</a>
	</li>
	<li>
		<a href="/staff/system/log/search?userId={{ $user->id }}" title="Logs" class="button">
			<img src="/img/icons/logs.png" alt="" /> Logs
		</a>
	</li>
	<li>
		<a href="/sudo-fix-problem/{{ $user->id }}" title="sudo fix-problem" class="button alert warning">
			<img src="/img/icons/sin.png" alt="" /> Solve problem (experimental)
		</a>
	</li>
	<li>
		<a href="/staff/user/user/{{ $user->id }}/remove" title="Remove" class="button alert remove confirm">
			<img src="/img/icons/remove.png" alt="" /> Remove user
		</a>
	</li>
</ul>

<fieldset>
	<legend>Information</legend>
	<div class="row">
		<div class="large-2 medium-3 small-12 column">
			<label>UID:
				<input type="number" name="uid" value="{{ $user->uid }}" min="{{ $user->uid }}" max="{{ $user->uid }}" readonly />
			</label>
		</div>
		<div class="large-4 medium-9 small-12 column">
			<label>Username:
				<input type="text" name="username" value="{{ $userInfo->username }}" readonly />
			</label>
		</div>
		<div class="large-6 medium-12 small-12 column">
			<label>Home directory:
				<input type="text" name="homedir" value="{{ $user->homedir }}" readonly />
			</label>
		</div>
	</div>
	<div class="row">
		<div class="large-4 medium-12 small-12 column">
			<label>E-mail address:
				<input type="email" name="email" value="{{ $userInfo->email }}" readonly />
			</label>
		</div>
		<div class="large-4 medium-6 small-12 column">
			<label>First name:
				<input type="text" name="fname" value="{{ $userInfo->fname }}" readonly />
			</label>
		</div>
		<div class="large-4 medium-6 small-12 column">
			<label>Surname:
				<input type="text" name="lname" value="{{ $userInfo->lname }}" readonly />
			</label>
		</div>
	</div>
	<div class="row">
		<div class="large-4 medium-4 small-12 column">
			<label>Student number:
				<input type="text" name="rnummer" value="{{ $userInfo->schoolnr }}" readonly />
			</label>
		</div>
		<div class="large-4 medium-4 small-12 column">
			<label>Shell:
				<input type="text" name="shell" value="{{ $user->shell }}" readonly />
			</label>
		</div>
		<div class="large-4 medium-4 small-12 column">
			<label>E-mail:
				<input type="text" name="shell" value="{{ $userMailEnabledPretty }}" readonly />
			</label>
		</div>
	</div>
	<div class="row">
		<div class="large-4 medium-4 small-12 column">
			<label>Hashing algorithm:
				<input type="text" name="algorithm" value="{{ $cryptAlgorithmPretty }}" readonly />
			</label>
		</div>
		<div class="large-8 medium-8 small-12 column">
			<label>Gecos:
				<input type="text" name="gcos" value="{{ $user->gcos }}" readonly />
			</label>
		</div>
	</div>
	<div class="row">
		<div class="large-6 medium-6 small-12 column">
			<label>Validation link (for renewal):
				@if (empty ($userInfo->validationcode))
				<p class="alert-box info">User has not requested a renewal</p>
				@else
				<input type="text" name="validationcode" value="{{ url ('/user/' . $user->id . '/expired/renew/' . $userInfo->validationcode) }}" readonly />
				@endif
			</label>
		</div>
		<div class="large-6 medium-6 small-12 column">
			<label>Single-use login link:
				@if (empty ($userInfo->logintoken))
					@if (empty ($user) || $userInfo->validated == 0)
					<p class="alert-box info">User has not been validated yet</p>
					@elseif ($user->hasExpired ())
					<p class="alert-box info">Account expired. Set a temporary password so the user can renew their account.</p>
					@else
					<br />
					<a href="/staff/user/user/{{ $user->id }}/more/loginToken" class="button radius"><img src="/img/icons/generate.png" alt="" /> Generate</a>
					@endif
				@else
					<input type="text" name="logintoken" value="{{ url ('/user/' . $user->id . '/amnesia/login/' . $userInfo->logintoken) }}" readonly />
				@endif
			</label>
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
							{{ $user->gid == $group->gid ? '<img src="/img/icons/ok.png" alt="Member" />' : '' }}
						</td>
						<td>
							{{ $user->isGroupMember ($group) ? '<img src="/img/icons/ok.png" alt="Member" />' : '' }}
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
</fieldset>
@endsection
