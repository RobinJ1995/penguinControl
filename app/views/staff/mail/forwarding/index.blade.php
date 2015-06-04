@extends ('layout.master')

@section ('pageTitle')
Doorstuuradressen &bull; Staff
@endsection

@section ('content')
{{ $mFwds->links () }}
<table>
	<thead>
		<tr>
			<th></th>
			<th>E-mailadres</th>
			<th>Bestemming</th>
			<th>Gebruiker</th>
		</tr>
	</thead>
	<tbody>
		@foreach ($mFwds as $mFwd)
		<tr>
			<td>
				<div class="button-group radius">
					<a href="/staff/mail/forwarding/{{ $mFwd->id }}/edit" title="Bewerken" class="button tiny">
						<img src="/img/icons/edit.png" alt="Bewerken" />
					</a><a href="/staff/mail/forwarding/{{ $mFwd->id }}/remove" title="Verwijderen" class="button tiny alert remove">
						<img src="/img/icons/remove.png" alt="Verwijderen" />
					</a>
				</div>
			</td>
			<td>{{ $mFwd->source }}</td>
			<td>{{ $mFwd->destination }}</td>
			<td>
				<span class="{{ $mFwd->getUser ()->gid < Group::where ('name', 'user')->firstOrFail ()->gid ? 'label' : '' }}">{{ $mFwd->getUser ()->userInfo->username }}</span>
			</td>
		</tr>
		@endforeach
	</tbody>
</table>
{{ $mFwds->links () }}
<div class="right">
	<a href="/staff/mail/forwarding/create" title="Toevoegen" class="button radius">
		<img src="/img/icons/add.png" alt="Toevoegen" />
	</a>
</div>

@include ('staff.mail.search_part')
@endsection
