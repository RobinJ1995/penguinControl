@extends ('layout.master')

@section ('pageTitle')
Databases
@endsection

@section ('content')
<div class="row" style="margin-top: 3px;">
	<div class="large-8 medium-7 small-12 column">
		<p>Voor het beheren van de databases maakt SIN gebruik van PHPMyAdmin. Hier kan je databases aanmaken en beheren voor gebruik in je dynamische toepassingen.</p>
	</div>
	<div class="large-4 medium-5 small-12 column">
		<form action="http://sql.sinners.be/" method="POST" id="phpMyAdminPost">
			<input type="hidden" name="pma_username" value="{{ htmlentities ($dbUsername) }}" />
			<input type="hidden" name="pma_password" value="{{ htmlentities ($dbPassword) }}" />
			<button>Ga naar PHPMyAdmin</button>
		</form>
	</div>
</div>
<script type="text/javascript">
	document.getElementById ('phpMyAdminPost').submit ();
</script>
@endsection
