@extends ('layout.master')

@section ('pageTitle')
E-mail
@endsection

@section ('content')
<form action="/mail" method="POST">
	<div class="row">
		<div class="large-9 medium-8 small-12 column">
			<p>To prevent abuse, e-mail is disabled for every account by default. Currently, e-mail has been enabled for your account. If you do not use e-mail, however, it is recommended that you disable it here so it can not be abused.</p>
			<p>Note: Disabling e-mail here will not prevent your websites from sending automated e-mails.</p>
		</div>
		<div class="large-3 medium-4 small-12 column">
			{{ Form::token () }}
			<button name="disable" value="{{ time () }}" style="margin-top: 3px;">Disable e-mail</button>
		</div>
	</div>
	@section ('custom_fields')
	@show
</form>
@endsection