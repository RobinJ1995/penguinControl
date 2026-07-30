@extends ('layout.master')

@section ('pageTitle')
Add system task &bull; Staff
@endsection

@section ('content')
<form action="/staff/system/systemtask/create" method="POST" data-abide>
	<fieldset>
		<legend>Add system task</legend>
		<div class="row">
			<div class="large-12 medium-12 small-12 column">
				<label>Type:
					{{ Form::select
					(
						'type',
						array
						(
							SystemTask::TYPE_APACHE_RELOAD => 'Reload web server configuration',
							SystemTask::TYPE_NUKE_EXPIRED_VHOSTS => 'Disable expired users\' websites',
							SystemTask::TYPE_CALCULATE_DISK_USAGE => 'Recalculate users\' disk usage'
						),
						old ('type')
					)
					}}
				</label>
				<small class="error">Invalid input</small>
			</div>
		</div>
		<div class="row">
			<div class="large-6 medium-6 small-12 column">
				<label>Start:
					<input type="text" name="start" value="{{ old ('start') }}" placeholder="DD-MM-YYYY HH:MM:SS" />
				</label>
				<small class="error">Invalid input</small>
			</div>
			<div class="large-6 medium-6 small-12 column">
				<label>End:
					<input type="text" name="end" value="{{ old ('end') }}" placeholder="DD-MM-YYYY HH:MM:SS" />
				</label>
				<small class="error">Invalid input</small>
			</div>
		</div>
		<div class="row">
			<div class="large-6 medium-6 small-12 column">
				<label>Interval:
					<input type="number" name="interval" value="{{ old ('interval') }}" />
				</label>
				<small class="error">Invalid input</small>
			</div>
			<div class="large-6 medium-6 small-12 column">
				<label>&nbsp;
					{{ Form::select
					(
						'interval_unit',
						array
						(
							'sec' => 'seconds',
							'min' => 'minutes',
							'hour' => 'uur',
							'day' => 'days',
							'week' => 'weeks'
						),
						old ('interval_unit')
					)
					}}
				</label>
				<small class="error">Invalid input</small>
			</div>
		</div>
		<div>
			{{ Form::token () }}
			<button name="save" value="{{ time () }}">Save</button>
		</div>
	</fieldset>
</form>
@endsection