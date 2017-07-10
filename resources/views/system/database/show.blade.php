@extends ('layout.master')

@section ('pageTitle')
Databases
@endsection

@section ('content')
<div class="row" style="margin-top: 3px;">
	<div class="large-8 medium-7 small-12 column">
		<p>PHPMyAdmin is used for managing your databases.</p>
	</div>
	<div class="large-4 medium-5 small-12 column">
		<form action="{{ $phpmyadminUrl }}" method="POST" id="phpMyAdminPost">
			<input type="hidden" name="pma_username" value="{{ htmlentities ($dbUsername) }}" />
			<input type="hidden" name="pma_password" value="{{ htmlentities ($dbPassword) }}" />
			<button>Go to PHPMyAdmin</button>
		</form>
	</div>
</div>
<script type="text/javascript">
	document.getElementById ('phpMyAdminPost').submit ();
</script>
@endsection