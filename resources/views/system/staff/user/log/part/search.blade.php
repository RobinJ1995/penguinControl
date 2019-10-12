<div id="modalSearch" class="reveal-modal" data-reveal>
	<div class="row">
		<div class="large-12 column">
			<h2>Search</h2>

			<form action="{{ $searchUrl }}" method="GET">
				<div class="row">
					<div class="large-6 medium-12 column">
						<label>Username:
							<input type="text" name="username" />
						</label>
					</div>
					<div class="large-6 medium-12 column">
						<label>Name:
							<input type="text" name="name" />
						</label>
					</div>
				</div>
				<div class="row">
					<div class="large-6 medium-12 column">
						<label>E-mail address:
							<input type="text" name="email" />
						</label>
					</div>
					<div class="large-6 medium-12 column">
						<label>Primary group:
							{{
								Form::select
								(
									'gid',
									array ('' => 'All') + Group::pluck ('name', 'gid')->toArray ()
								)
							}}
						</label>
					</div>
				</div>
				<div class="row">
					<div class="large-6 medium-12 column">
						<label>From:
							<input type="date" name="time_van" />
						</label>
					</div>
					<div class="large-6 medium-12 column">
						<label>To:
							<input type="date" name="time_tot" />
						</label>
					</div>
				</div>
				<div class="row">
					<div class="large-6 medium-12 column">
						<label>Billing status:
							{{ Form::select
								(
									'status',
									array ('all' => 'All') + $statusMeaning
								)
							}}
						</label>
					</div>
					<div class="large-6 medium-12 column">
						<label>New:
							{{ Form::select
								(
									'new',
									array
									(
										'all' => 'All',
										'0' => 'No',
										'1' => 'Yes',
									)
								)
							}}
						</label>
					</div>
				</div>
				<div class="row">
					<div class="large-6 medium-12 column">
						<label>
							<br />
							<input type="checkbox" name="pagination" value="true" checked="checked" /> Paginate search results
						</label>
					</div>
				</div>

				<button>Search</button>
		</div>
	</div>
	</form>

	<a class="close-reveal-modal">&#215;</a>
</div>
