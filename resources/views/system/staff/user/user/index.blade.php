@extends ('layout.master')

@section ('pageTitle')
Users &bull; Staff
@endsection

@section ('content')
<div data-magellan-expedition="fixed">
	<dl class="sub-nav">
		<dd data-magellan-arrival="build">
			<a href="#users">Users ({{ $usersCount }})</a>
		</dd>
		<dd data-magellan-arrival="build">
			<a href="#expired">Expired ({{ $expiredCount }})</a>
		</dd>
		<dd data-magellan-arrival="js">
			<a href="#pending">Awaiting validation ({{ $pendingCount }})</a>
		</dd>
	</dl>
</div>

<fieldset>
	<legend id="users">Users</legend>
	<?php //TODO//Paginator::setPageName ('user_page'); ?>
	{{ $users->links () }}
	<table>
		<thead>
			<tr>
				<th></th>
				<th>
					<a href="{{ $url }}/order/uid">UID</a>
				</th>
				<th>
					Username
				</th>
				<th>
					Name
				</th>
				<th>
					Student number
				</th>
				<th>
					<a href="{{ $url }}/order/gid">Primary group</a>
				</th>
			</tr>
		</thead>
		<tbody>
			@foreach ($users as $user)
			<tr>
				<td>
					<div class="button-group radius">
						<a href="/staff/user/user/{{ $user->id }}/more" title="More..." class="button tiny">
							<img src="/img/icons/more.png" alt="More..." />
						</a><a href="/staff/user/user/{{ $user->id }}/expire" title="Change expiry date" class="button tiny">
							<img src="/img/icons/expire.png" alt="Expire" />
						</a><a href="/staff/user/user/{{ $user->id }}/edit" title="Edit" class="button tiny">
							<img src="/img/icons/edit.png" alt="Edit" />
						</a>
					</div>
				</td>	
				<td>{{ $user->uid }}</td>
				<td>{{ $user->userInfo->username }}</td>
				<td>{{ $user->userInfo->getFullName () }}</td>
				<td>{{ $user->userInfo->schoolnr }}</td>
				<td>
					<span class="{{ $user->gid < Group::where ('name', 'user')->firstOrFail ()->gid ? 'label' : '' }}">{{ ucfirst ($user->primaryGroup->name) }}</span>
				</td>
			</tr>
			@endforeach
		</tbody>
	</table>
	{{ $users->links () }}
	<div class="right">
		<a href="/staff/user/user/create" title="Add" class="button radius">
			<img src="/img/icons/add.png" alt="Add" />
		</a>
	</div>
</fieldset>

<fieldset>
	<legend id="expired">Expired</legend>
	<?php //TODO//Paginator::setPageName ('expired_page'); ?>
	{{ $expired->links () }}
	<table>
		<thead>
			<tr>
				<th></th>
				<th>
					<a href="{{ $url }}/order/uid">UID</a>
				</th>
				<th>
					Username
				</th>
				<th>
					Name
				</th>
				<th>
					Student number
				</th>
				<th>
					<a href="{{ $url }}/order/gid">Primary group</a>
				</th>
			</tr>
		</thead>
		<tbody>
			@foreach ($expired as $user)
			<tr class="expired">
				<td>
					<div class="button-group radius">
						<a href="/staff/user/user/{{ $user->id }}/more" title="More..." class="button tiny">
							<img src="/img/icons/more.png" alt="More..." />
						</a><a href="/staff/user/user/{{ $user->id }}/expire" title="Change expiry date" class="button tiny alert">
							<img src="/img/icons/expire.png" alt="Expire" />
						</a><a href="/staff/user/user/{{ $user->id }}/edit" title="Edit" class="button tiny">
							<img src="/img/icons/edit.png" alt="Edit" />
						</a>
					</div>
				</td>
				<td>{{ $user->uid }}</td>
				<td>{{ $user->userInfo->username }}</td>
				<td>{{ $user->userInfo->getFullName () }}</td>
				<td>{{ $user->userInfo->schoolnr }}</td>
				<td>
					<span class="{{ $user->gid < Group::where ('name', 'user')->firstOrFail ()->gid ? 'label' : '' }}">{{ ucfirst ($user->primaryGroup->name) }}</span>
				</td>
			</tr>
			@endforeach
		</tbody>
	</table>
	{{ $expired->links () }}
</fieldset>

<fieldset>
	<legend id="pending">Awaiting validation</legend>
	<?php //TODO//Paginator::setPageName ('pending_page'); ?>
	{{ $pending->links () }}
	<table>
		<thead>
			<tr>
				<th></th>
				<th>Username</th>
				<th>Name</th>
				<th>E-mail address</th>
				<th>Student number</th>
			</tr>
		</thead>
		<tbody>
			@foreach ($pending as $user) {{-- Note: $user is a UserInfo here, not a User --}}
			<tr>
				<td>
					<div class="button-group radius">
						<a href="/staff/user/user/{{ $user->id }}/validate" title="Validate" class="button tiny">
							<img src="/img/icons/validate.png" alt="Validate" />
						</a><a href="/staff/user/user/{{ $user->id }}/reject" title="Reject" class="button tiny alert remove confirm">
							<img src="/img/icons/reject.png" alt="Reject" />
						</a>
					</div>
				</td>
				<td>{{ $user->username }}</td>
				<td>{{ $user->getFullName () }}</td>
				<td>{{ $user->email }}</td>
				<td>{{ $user->schoolnr }}</td>
			</tr>
			@endforeach
		</tbody>
	</table>
	{{ $pending->links () }}
</fieldset>

<div id="modalSearch" class="reveal-modal" data-reveal>
	<h2>Search</h2>
	
	<form action="{{ $searchUrl }}" method="GET">
		<label>Username:
			<input type="text" name="username" />
		</label>
		<label>Name:
			<input type="text" name="name" />
		</label>
		<label>E-mail address:
			<input type="text" name="email" />
		</label>
		<label>Student number:
			<input type="text" name="schoolnr" />
		</label>
		<label>
			<input type="checkbox" name="validationcode" /> Has an unused renewal validation code
		</label>
		<label>
			<input type="checkbox" name="logintoken" /> Has an unused one-time login link
		</label>
		
		<button>Search</button>
	</form>
	
	<a class="close-reveal-modal">&#215;</a>
</div>
@endsection