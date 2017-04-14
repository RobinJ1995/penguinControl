@extends ('layout.master')

@section ('pageTitle')
Create vHost
@endsection

@section ('content')
<form action="/website/vhost/create" method="POST" data-abide>
	<fieldset>
		<legend>Create vHost</legend>
		<div class="row">
			<div class="large-5 medium-12 small-12 column">
				<label>Host:
					<input type="text" name="servername" value="{{ Input::old ('servername') }}" required />
				</label>
				<small class="error">Invalid input</small>
			</div>
			<div class="large-7 medium-12 small-12 column">
				<label>Document root:
					<div class="row collapse">
						<div class="large-5 medium-5 small-12 column">
							<span class="prefix">{{ $user->homedir }}/</span>
						</div>
						<div class="large-7 medium-7 small-12 column">
							<input type="text" name="docroot" value="{{ Input::old ('docroot') }}" />
						</div>
					</div>
				</label>
				<small class="error">Invalid input</small>
			</div>
		</div>
		<div class="row">
			<div class="large-12 column">
				<label>Aliases:
					<small>(Separate multiple aliases with spaces.)</small>
					<input type="text" name="serveralias" value="{{ Input::old ('serveralias') }}" />
				</label>
				<small class="error">Invalid input</small>
			</div>
		</div>
		<div class="row">
			<div class="large-6 medium-7 small-12 column">
				<label>Protocol:
					<small>(Make sure you have the DNS records setup correctly before enabling HTTPS.)</small>
					{{ Form::select
					(
						'ssl',
						array
						(
							'0' => 'HTTP',
							'1' => 'HTTP + HTTPS',
							'2' => 'HTTPS with redirect'
						),
						Input::old ('ssl', 0)
					)
					}}
				</label>
				<small class="error">Invalid input</small>
			</div>
			<div class="large-6 medium-5 small-12 column">
				<label>CGI:
					{{ Form::select
					(
						'cgi',
						array
						(
							'0' => 'Off',
							'1' => 'On'
						),
						Input::old ('cgi', 0)
					)
					}}
				</label>
				<small class="error">Invalid input</small>
			</div>
		</div>
		<div class="row">
			<div class="large-12 column">
				<label>
					<input type="checkbox" name="installWordpress" value="true" />
					Install Wordpress on this vHost
				</label>
			</div>
		</div>
		<div>
			{{ Form::token () }}
			<button name="save" value="{{ time () }}">Save</button>
		</div>
	</fieldset>
</form>
@endsection