<!doctype html>
<html class="no-js" lang="en">
	<head>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<title>Fout &bull; SIN</title>
		<link rel="shortcut icon" href="/img/favicon.ico?adsdfasf" type="image/x-icon" />
		<style type="text/css">
			body
			{
				background: blue;
				color: white;
				font-family: mono;
				font-size: 16px;
			}
			h1, h2, h3
			{
				font-size: 16px;
			}
			a
			{
				color: white;
				text-decoration: underline;
			}
		</style>
	</head>
	<body>
		<div id="bsod">
			<h1>Fout</h1>
			<p>Er is iets misgelopen, en de gevraagde actie is afgebroken om verdere problemen te voorkomen.</p>
			@if ($mailSent === true)
				<p>Onze systeembeerders hebben een bericht ontvangen met meer informatie over het probleem en zullen de nodige acties ondernemen om dit in de toekomst te voorkomen.</p>
			@endif
			<p>Wij bieden onze excuses aan voor het ongemak.<br />
			-- Het SIN-team</p>
			<h2>Details</h2>
			@if (! empty ($alerts))
				@foreach ($alerts as $alert)
					<p>{{ $alert->getMessage () }}</p>
				@endforeach
			@endif
			@if (! empty ($ex))
				<p>{{ $ex }}</p>
			@endif
			<p><a href="/home" onClick="history.back ();">Klik hier om terug te gaan</a></p>
		</div>
	</body>
</html>
