<!doctype html>
<html class="no-js" lang="en">
	<head>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<title>Error</title>
		<link rel="shortcut icon" href="/img/favicon.ico" type="image/x-icon" />
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
			<h1>Whoops!</h1>
			<p>Something went wrong! The system's stopped doing whatever it was doing to prevent further problems. Sorry about that!</p>
			@if ($mailSent === true)
				<p>Our system administrators have been notified of the problem and will try to fix it as soon as possible.</p>
			@endif
			<h2>Details</h2>
			@if (! empty ($alerts))
				@foreach ($alerts as $alert)
					<p>{{ $alert->message }}</p>
				@endforeach
			@endif
			@if (! empty ($ex))
				<p>{!! $ex !!}</p>
			@endif
			<p><a href="/home" onClick="history.back ();">Click here to go back.</a></p>
		</div>
	</body>
</html>
