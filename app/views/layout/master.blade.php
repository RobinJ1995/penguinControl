<!doctype html>
<html class="no-js" lang="en">
	<head>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<title>@section ('pageTitle')
			<!-)>
			@show
			&bull; SIN</title>
		<link rel="shortcut icon" href="/img/favicon.ico" type="image/x-icon" />
		@section ('css')
		<link rel="stylesheet" href="/css/foundation.css" />
		<link rel="stylesheet" href="/css/stylesheet.css" />
		<link rel="stylesheet" href="/foundation-icons/foundation-icons.css" />
		@show
		@section ('js')
		<script src="/js/vendor/modernizr.js"></script>
		<script src="/js/vendor/jquery.js"></script>
		<script src="/js/removeConfirm.js"></script>
		<script src="/js/foundation/foundation.js"></script>
		<script src="/js/foundation/foundation.interchange.js"></script>
		<script src="/js/foundation/foundation.dropdown.js"></script>
		<script src="/js/foundation/foundation.topbar.js"></script>
		<script src="/js/foundation/foundation.orbit.js"></script>
		<script src="/js/foundation/foundation.abide.js"></script>
		<script src="/js/foundation/foundation.equalizer.js"></script>
		<script src="/js/foundation/foundation.alert.js"></script>
		<script src="/js/foundation/foundation.magellan.js"></script>
		<script src="/js/foundation/foundation.reveal.js"></script>
		<script src="/js/browserUpdate.js"></script>
		<script src="/js/a29uYW1p.js"></script>
		<script>
			$(document).ready
			(
				function ()
				{
					{{--
						# Non-obfuscated version #
						@if (! empty ($konami))
							$('#clippy').addClass ('persistent');
						@endif

						var konami = new Konami
						(
							function ()
							{
								$('#clippy').addClass ('konami');

								$('#clippy').click
								(
									function ()
									{
										$('body').addClass ('konami');
									}
								);
							}
						);	
					--}}
					
					@if (! empty ($konami))
						eval(function(p,a,c,k,e,d){e=function(c){return c};if(!''.replace(/^/,String)){while(c--){d[c]=k[c]||c}k=[function(e){return d[e]}];e=function(){return'\\w+'};c=1};while(c--){if(k[c]){p=p.replace(new RegExp('\\b'+e(c)+'\\b','g'),k[c])}}return p}('$(\'#0\').1(\'2\');',3,3,'clippy|addClass|persistent'.split('|'),0,{}))
					@endif

					eval(function(p,a,c,k,e,d){e=function(c){return c};if(!''.replace(/^/,String)){while(c--){d[c]=k[c]||c}k=[function(e){return d[e]}];e=function(){return'\\w+'};c=1};while(c--){if(k[c]){p=p.replace(new RegExp('\\b'+e(c)+'\\b','g'),k[c])}}return p}('4 0=5 8(1(){$(\'#3\').2(\'0\');$(\'#3\').6(1(){$(\'7\').2(\'0\')})});',9,9,'konami|function|addClass|clippy|var|new|click|body|Konami'.split('|'),0,{}))
				}
			);
		</script>
		@show
		@section ('holidays')
		@include ('layout.holidays')
		@show
	</head>
	<body>
		{{--<div class="row">
			<div class="alert-box info" data-alert>De nieuwe versie van SINControl is nog in testfase. Sommige dingen zullen dus nog niet helemaal werken. Bij problemen, <a href="/page/contact">contacteer ons</a>.</div>
		</div>--}}
		
		<header class="row">
			<div class="large-3 columns">
				<h1>
					<a href="/home"><img src="/img/logo.png" alt="<!-)>" /></a>
				</h1>
			</div>
			<div class="large-9 columns">
				<ul class="right button-group">
					@section ('topMenu')
					@if (! empty ($searchUrl))
					<li>
						<a href="#" class="button" data-reveal-id="modalSearch">
							<i class="fi-magnifying-glass"></i>
						</a>
					</li>
					@endif
					<li>
						<a href="/home" class="button">Home</a>
					</li>
					<li>
						<a href="https://cloud.sinners.be/" class="button">SIN Cloud</a>
					</li>
					@show
				</ul>
			</div>
		</header>

		<div class="row">
			@section ('controlMenu')
			@include ('controlMenu')
			@show
			
			@section ('siteMenu')
			@show
			
			@section ('staffMenu')
			@include ('staffMenu')
			@show
		</div>

		<div class="row">
			@section ('alerts')
			@if (! empty ($alerts))
			@foreach ($alerts as $alert)
			<div class="alert-box {{ $alert->getType () }}" data-alert>
				{{ $alert->getMessage () }}
				@if ($alert->getClose ())
				<a href="#" class="close">&times;</a>
				@endif
			</div>
			@endforeach
			@endif
			@section ('alerts')
			@if (! empty (Session::get ('alerts')))
			@foreach (Session::get ('alerts') as $alert)
			<div class="alert-box {{ $alert->getType () }}" data-alert>
				{{ $alert->getMessage () }}
				@if ($alert->getClose ())
				<a href="#" class="close">&times;</a>
				@endif
			</div>
			@endforeach
			@endif
			@if (! empty ($errors))
			@foreach ($errors->all () as $error)
			<div class="alert-box alert" data-alert>
				{{ $error }}
			</div>
			@endforeach
			@endif
			@show
		</div>

		<div class="row">
			<div id="content" class="large-12 column">
				@section ('content')
				@show
			</div>
		</div>

		<footer class="row">
			<hr />
			<div class="large-6 column">
				<p>&copy; Student Information Network</p>
			</div>
			<div class="large-6 column">
				<ul class="inline-list right">
					@section ('footerLinks')
					<li><a href="/page/contact">Contact</a></li>
					@show
				</ul>
			</div>
		</footer>

		<div id="clippy"></div>
		
		<script>
		$(document).foundation
		(
			{
				topbar:
					{
						custom_back_text: true,
						back_text: '&laquo; Terug'
					}
			}
		);
		</script>
	</body>
</html>
