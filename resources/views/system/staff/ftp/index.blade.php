@extends ('layout.master')

@section ('pageTitle')
FTP-accounts &bull; Staff
@endsection

@section ('content')
{{ $ftps->links () }}
<table>
	<thead>
		<tr>
			<th></th>
			<th>Username</th>
			<th>Directory</th>
			<th>Owner</th>
		</tr>
	</thead>
	<tbody>
		@foreach ($ftps as $ftp)
		<tr>
			<td>
				<div class="button-group radius">
					<a href="/staff/ftp/{{ $ftp->id }}/edit" title="Edit" class="button tiny">
						<img src="/img/icons/edit.png" alt="Edit" />
					</a><a href="/staff/ftp/{{ $ftp->id }}/remove" title="Remove" class="button tiny alert remove">
						<img src="/img/icons/remove.png" alt="Remove" />
					</a>
				</div>
			</td>
			<td>
				@if ($ftp->locked)
					<img src="/img/icons/locked.png" alt="[Locked]" />
				@endif
				{{ $ftp->username }}
			</td>
			<td>{{ $ftp->dir }}</td>
			<td>
				<span class="{{ $ftp->user->gid < Group::where ('name', 'user')->firstOrFail ()->gid ? 'label' : '' }}">{{ $ftp->user->userInfo->username }}</span>
			</td>
		</tr>
		@endforeach
	</tbody>
</table>
{{ $ftps->links () }}
<div class="right">
	<a href="/staff/ftp/create" title="Add" class="button radius">
		<img src="/img/icons/add.png" alt="Add" />
	</a>
</div>

<div id="modalSearch" class="reveal-modal" data-reveal>
	<h2>Search</h2>
	
	<form action="{{ $searchUrl }}" method="GET">
		<label>Username:
			<input type="text" name="user" />
		</label>
		<label>Directory:
			<input type="text" name="dir" />
		</label>
		<label>User:
			<input type="text" name="username" />
		</label>
		
		<button>Search</button>
	</form>
	
	<a class="close-reveal-modal">&#215;</a>
</div>
@endsection