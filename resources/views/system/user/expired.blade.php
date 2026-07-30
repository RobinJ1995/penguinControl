@extends ('layout.master')

@section ('pageTitle')
Account expired
@endsection

@section ('content')
<div class="large-3 medium-3 hide-for-small-down column">
	<br />
</div>
<div class="large-6 medium-6 small-12 column">
	{{--
		This used to be a self-service renewal form: a user confirmed their password,
		was mailed a confirmation link, and following it moved their expiry to the next
		1 October. That date was the start of an academic year and the renewal existed
		to track a yearly membership fee, so neither survives here //
	--}}
	<p class="alert-box info">
		This account has expired. Contact an administrator to have it reactivated.
	</p>
</div>
<div class="large-3 medium-3 hide-for-small-down column">
	<br />
</div>
@endsection
