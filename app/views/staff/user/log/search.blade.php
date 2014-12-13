@extends ('layout.master')

@section ('css')
@parent
<link rel="stylesheet" media="print" href="/css/print.css" />
@endsection

@section ('pageTitle')
Facturatie &bull; Staff
@endsection

@section ('js')
@parent
<script type="text/javascript">
	function clearExtraFields(tmpSubmitVal)
	{
		$('#log input[name="exportFields[]"]').remove();
		$('form#log input[name="exportBoekhouding"]').val ();
		$('#log input[name="action"]').val(tmpSubmitVal);
		$('#log').removeAttr('target');
		$('#exportError').hide();
		$('#exportError').html();
	}

	$(document).ready(function () {
		$('#exportError').hide();
		$('#selectAllUserLog').change(function () {
			$('input[name="userLogId[]"]').prop('checked', $(this).prop("checked"));
		});

		$('input[name="userLogId[]"]').change(function () {
			$('#selectAllUserLog').prop('checked', false);
		});
		
		$(document).on('closed.fndtn.reveal', '[data-reveal]', function () {
			clearExtraFields('facturatie');
		      });

		$('#export').submit(function (event) {
			event.preventDefault();

			clearExtraFields('facturatie');
			if ($('#log input[name="userLogId[]"]:checked').length > 0)
			{
				$('#exportError').hide();
				$('#exportError').html();
				$.each($('#export input[name="exportFields[]"]'), function (i, val) {
					if ($(this).prop('checked')) {
						$('#log').append('<input name="exportFields[]" type="hidden" value="' + $(this).val() + '"/>');
					}
				});

				var tmpSubmitVal = $('#log input[name="action"]').val();
				$('#log input[name="action"]').val('export');
				$('#log').attr('target', '_blank');

				$('#modalExportSettings').foundation('reveal', 'close');

				var exportBoekhouding = $('form#export select[name="exportBoekhouding"]').val ();
				$('form#log input[name="exportBoekhouding"]').val (exportBoekhouding);

				//submit form
				$('form#log input[name="submit"]').click();


				clearExtraFields(tmpSubmitVal);
				$('#log input[name="action"]').val(tmpSubmitVal);
				$('#log').removeAttr('target');
				location.reload();
			}
			else
			{
				$('#exportError').html('Selecteer ten minste één gebruiker!');
				$('#exportError').show();
			}
			
		});
	});
</script>
@endsection

@section ('content')
<p>{{ $count }} zoekresultaten</p>

{{ $paginationOn ? $userlogs->links () : '' }}
<form id="log" action="/staff/user/log/edit/checked" method="post">
	<table>
		<thead>
			<tr>
				<th></th>
				<th>
					Gebruikersnaam
				</th>
				<th>
					r-nummer
				</th>
				<th>
					Datum/Tijd
				</th>
				<th>
					Nieuw
				</th>
				<th>
					Facturatiestatus
				</th>
				<th>
					Primaire groep
				</th>
				<th>
					<input type="checkbox" id="selectAllUserLog" value="true">
				</th>
			</tr>
		</thead>
		<tbody>
			@foreach ($userlogs as $userlog)
			<tr>
				<td>
					<div class="button-group radius">
						<a href="/staff/user/log/{{ $userlog->id }}/edit" title="Bewerken" class="button tiny">
							<img src="/img/icons/edit.png" alt="Bewerken" />
						</a><a href="/staff/user/log/{{ $userlog->id }}/remove" title="Verwijderen" class="button tiny alert remove confirm">
							<img src="/img/icons/remove.png" alt="Verwijderen" />
						</a>
					</div>
				</td>
				<td>{{ $userlog->user_info->username }}</td>
				<td>{{ $userlog->user_info->schoolnr }}</td>
				<td>{{ $userlog->time }}</td>
				<td><img src="/img/icons/{{ $userlog->nieuw?'validate.png':'reject.png'; }}" alt="" /></td>
				<td>{{ $boekhoudingBetekenis[$userlog->boekhouding]}}</td>
				<td>
					@if (! empty ($userlog->user_info->user))
					<span class="{{ $userlog->user_info->user->gid < Group::where ('name', 'user')->firstOrFail ()->gid ? 'label' : '' }}">{{ ucfirst ($userlog->user_info->user->getGroup ()->name) }}</span>
					@endif
				</td>
				<td>
					<input type="checkbox" name="userLogId[]" value="{{ $userlog->id }}">
				</td>
			</tr>
			@endforeach
		</tbody>
	</table>
	{{ $paginationOn ? $userlogs->links () : '' }}

	<div class="right">
		<label>
			{{
				Form::select
				(
					'boekhouding',
					$boekhoudingBetekenis,
					0
				)
			}}
		</label>
		<input type="submit" name="submit" value="Wijzig facturatiestatus" class="button"/>
		<input type="hidden" name="action" value="facturatie"/>
		<input type="hidden" name="exportBoekhouding" value="" />
		<input type="button" value="Exporteren" data-reveal-id="modalExportSettings" class="button"/>
	</div>
</form>

<div id="modalSearch" class="reveal-modal" data-reveal>
	<div class="row">
		<div class="large-12 column">
			<h2>Zoeken</h2>

			<form action="{{ $searchUrl }}" method="GET">
				<div class="row">
					<div class="large-6 medium-12 column">
						<label>Gebruikersnaam:
							<input type="text" name="username" />
						</label>
					</div>
					<div class="large-6 medium-12 column">
						<label>Naam:
							<input type="text" name="name" />
						</label>
					</div>
				</div>
				<div class="row">
					<div class="large-6 medium-12 column">
						<label>E-mailadres:
							<input type="text" name="email" />
						</label>
					</div>
					<div class="large-6 medium-12 column">
						<label>Studentnummer:
							<input type="text" name="schoolnr" />
						</label>
					</div>
				</div>
				<div class="row">
					<div class="large-6 medium-12 column">
						<label>Van:
							<input type="date" name="time_van" />
						</label>
					</div>
					<div class="large-6 medium-12 column">
						<label>Tot:
							<input type="date" name="time_tot" />
						</label>
					</div>
				</div>
				<div class="row">
					<div class="large-6 medium-12 column">
						<label>Gefactureerd:
							{{ Form::select
								(
									'boekhouding',
									array
									(
										'all' => 'Alles',
										'-1'=>'Niet te factureren',
										'0'=>'Nog te factureren',
										'1'=>'Gefactureerd'
									)
								)
							}}
						</label>
					</div>
					<div class="large-6 medium-12 column">
						<label>Nieuw:
							{{ Form::select
								(
									'nieuw',
									array
									(
										'all' => 'Alles',
										'0' => 'Nee',
										'1' => 'Ja',
									)
								)
							}}
						</label>
					</div>
				</div>
				<label>Pagination:
					<input type="checkbox" name="pagination" value="true" checked="checked"/> Pagination
				</label>

				<button>Zoeken</button>
			</form>
		</div>
	</div>
	
	<a class="close-reveal-modal">&#215;</a>
	</div>
</div>

<div id="modalExportSettings" class="reveal-modal" data-reveal>
	<div class="row">
		<div class="large-12 column">
			<h2>Exporteren</h2>
			<form id="export" action="#" method="post">
				<div class="row">
					<div class="large-6 medium-12 column">
						<label>
							<input type="checkbox" name="exportFields[]" value="user_info.fname" checked="checked"/> Voornaam
						</label>
					</div>
					<div class="large-6 medium-12 column">
						<label>
							<input type="checkbox" name="exportFields[]" value="user_info.lname" checked="checked"/> Achternaam
						</label>
					</div>
					
					<div class="large-6 medium-12 column">
						<label>
							<input type="checkbox" name="exportFields[]" value="user_info.schoolnr" checked="checked"/> R-nummer
						</label>
					</div>
					<div class="large-6 medium-12 column">
						<label>
							<input type="checkbox" name="exportFields[]" value="user_info.email"/> E-mailadres
						</label>
					</div>
					
					<div class="large-6 medium-12 column">
						<label>
							<input type="checkbox" name="exportFields[]" value="user_info.username"/> Gebruikersnaam
						</label>
					</div>
					<div class="large-6 medium-12 column">
						<label>
							<input type="checkbox" name="exportFields[]" value="user_log.time"/> Datum/tijd
						</label>
					</div>
					
					<div class="large-6 medium-12 column">
						<label>Facturatiestatus van geselecteerde items instellen:
							{{ Form::select
								(
									'exportBoekhouding',
									array ( 'unchanged' => 'Ongewijzigd laten' ) + $boekhoudingBetekenis,
									'unchanged'
								)
							}}
						</label>
					</div>
					<div class="large-6 medium-12 column">
						<label>
							<input type="submit" name="export" value="Exporteren" class="button"/>
						</label>
					</div>				
					<div class="large-6 medium-12 column">
						<small id="exportError" class="error"></small>
					</div>
				</div>
			</form>
		</div>
	</div>
	
	<a class="close-reveal-modal">&#215;</a>
	</div>
</div>
@endsection