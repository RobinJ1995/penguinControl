@extends ('layout.master')

@section ('pageTitle')
E-mail
@endsection

@section ('content')
<form action="/mail" method="POST">
	<div class="row">
		<div class="large-9 medium-8 small-12 column">
			<p>To prevent abuse, e-mail is disabled for every account by default. To enable e-mail for your account, please click the button.</p>
		</div>
		<div class="large-3 medium-4 small-12 column">
			{{ Form::token () }}
			<button class="alert" name="enable" value="{{ time () }}" style="margin-top: 3px;">Enable e-mail</button>
		</div>
	</div>
	@section ('custom_fields')
	@show
</form>
@endsection