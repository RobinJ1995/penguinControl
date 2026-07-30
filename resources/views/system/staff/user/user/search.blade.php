@extends ('layout.master')

@section ('pageTitle')
Users &bull; Staff
@endsection

@section ('content')
<fieldset>
	<legend>{{ $count }} search results</legend>
	
	{{ $results->withQueryString ()->links () }}
	<table>
		<thead>
			<tr>
				<th></th>
				<th>
					UID
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
					Primary group
				</th>
			</tr>
		</thead>
		<tbody>
			@foreach ($results as $userInfo)
			<?php $user = $userInfo->user; ?>
			@if (empty ($user))
			<tr>
				<td colspan="2">
					No matching user<br />
					(<kbd>user_info#{{ $userInfo->id }}</kbd>)</td>
				<td>{{ $userInfo->username }}</td>
				<td>{{ $userInfo->getFullName () }}</td>
				<td>{{ $userInfo->schoolnr }}</td>
				<td></td>
			</tr>
			@else
			<tr>
				<td>
					<div class="button-group radius">
						<a href="/staff/user/user/{{ $user->id }}/more" title="More..." class="button tiny">
							<img src="/img/icons/more.png" alt="More..." />
						</a><a href="/staff/user/user/{{ $user->id }}/expire" title="Change expiry date" class="button tiny {{ $user->hasExpired () ? 'alert' : '' }}">
							<img src="/img/icons/expire.png" alt="Expire" />
						</a><a href="/staff/user/user/{{ $user->id }}/edit" title="Edit" class="button tiny">
							<img src="/img/icons/edit.png" alt="Edit" />
						</a>
					</div>
				</td>
				<td>{{ $user->uid }}</td>
				<td>{{ $userInfo->username }}</td>
				<td>{{ $userInfo->getFullName () }}</td>
				<td>{{ $userInfo->schoolnr }}</td>
				<td>
					<span class="{{ $user->gid < Group::where ('name', 'user')->firstOrFail ()->gid ? 'label' : '' }}">{{ ucfirst ($user->primaryGroup->name) }}</span>
				</td>
			</tr>
			@endif
			@endforeach
		</tbody>
	</table>
	{{ $results->withQueryString ()->links () }}
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