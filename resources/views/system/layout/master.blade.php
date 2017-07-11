<!doctype html>
<html class="no-js" lang="en">
	<head>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<title>@section ('pageTitle')
			🐧
			@show
			&bull; 🐧control</title>
		<link rel="shortcut icon" href="/img/favicon.ico" type="image/x-icon" />
		@section ('css')
		<link rel="stylesheet" href="/css/foundation.css" />
		<link rel="stylesheet" href="/css/stylesheet.css" />
		<link rel="stylesheet" href="/foundation-icons/foundation-icons.css" />
		@if (App::environment ('local'))
		<style type="text/css">
			/*
			 * Gewoon om verwarring te voorkomen en een duidelijke
			 * indicatie te geven dat in deze tab de lokale versie
			 * draait ;-)
			 */
			
			h1 img
			{
				filter: hue-rotate(250deg);
			}
		</style>
		@endif
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
						<a href="/user/login" class="button">Login</a>
					</li>
					@show
				</ul>
			</div>
		</header>

		<div class="row">
			@section ('controlMenu')
			<div class="contain-to-grid sticky">
				<nav id="controlMenu" class="top-bar" data-topbar data-options="sticky_on: large">
					<ul class="title-area">
						<li class="name"></li>
						<li class="toggle-topbar menu-icon">
							<a href="#">
								<span>Menu</span>
							</a>
						</li>
					</ul>
					
					<section class="top-bar-section">
						<ul>
							<li class="has-dropdown">
								<a href="#">User</a>
								<ul class="dropdown">
									<li>
										<a href="/user/start">Information</a>
									</li>
									<li>
										<a href="/user/edit">Modify account</a>
									</li>
									<li>
										<a href="/user/logout">Logout</a>
									</li>
									@if (is_admin ())
									<li class="divider hide-for-small"></li>
									<li class="has-dropdown">
										<a href="#">Admin</a>
										<ul class="dropdown">
											<li>
												<a href="/staff/user/user">Users</a>
											</li>
											<li>
												<a href="/staff/user/log">Billing</a>
											</li>
											<li>
												<a href="/staff/user/limit">Limits</a>
											</li>
											<li>
												<a href="/staff/user/group">Groups</a>
											</li>
										</ul>
									</li>
									@endif
								</ul>
							</li>
							<li class="divider hide-for-small"></li>
							@if (is_feature_enabled ('vhost') || (is_feature_enabled ('website') && is_admin ()))
							<li class="has-dropdown">
								<a href="#">Websites</a>
								<ul class="dropdown">
									@if (is_feature_enabled ('vhost'))
									<li>
										<a href="/website/vhost">vHosts</a>
									</li>
									@endif
									@if (is_feature_enabled ('website') && is_admin ())
										<li>
											<a href="/staff/page">Pages</a>
										</li>
									@endif
								</ul>
							</li>
							<li class="divider hide-for-small"></li>
							@endif
							@if (is_feature_enabled ('ftp'))
							<li class="">
								<a href="/ftp">FTP</a>
							</li>
							<li class="divider hide-for-small"></li>
							@endif
							@if (is_feature_enabled ('database'))
							<li class="">
								<a href="/database">Databases</a>
							</li>
							<li class="divider hide-for-small"></li>
							@endif
							@if (is_feature_enabled ('mail'))
							<li class="has-dropdown">
								<a href="#">E-mail</a>
								<ul class="dropdown">
									<li>
										<a href="/mail">General</a>
									</li>
									<li>
										<a href="/mail/domain">Domains</a>
									</li>
									@if (is_feature_enabled ('mail_user'))
									<li>
										<a href="/mail/user">E-mail accounts</a>
									</li>
									@endif
									@if (is_feature_enabled ('mail_forward'))
									<li>
										<a href="/mail/forward">Forwarding addresses</a>
									</li>
									@endif
								</ul>
							</li>
							<li class="divider hide-for-small"></li>
							@endif
							@if (is_admin ())
							<li class="has-dropdown">
								<a href="#">System</a>
								<ul class="dropdown">
									<li>
										<a href="/staff/system/systemtask">System tasks</a>
									</li>
									<li>
										<a href="/staff/system/log">Logs</a>
									</li>
								</ul>
							</li>
							<li class="divider hide-for-small"></li>
							@endif
						</ul>
					</section>
				</nav>
			</div>
			@show
			
			@section ('siteMenu')
			@show
		</div>

		<div class="row">
			@section ('alerts')
			@if (! empty ($alerts))
			@foreach ($alerts as $alert)
			{!! $alert !!}
			@endforeach
			@endif
			@section ('alerts')
			@if (! empty (Session::get ('alerts')))
			@foreach (Session::get ('alerts') as $alert)
			{!! $alert !!}
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
				<br />
			</div>
			<div class="large-6 column">
				<ul class="inline-list right">
					@section ('footerLinks')
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
						back_text: '&laquo; Back'
					}
			}
		);
		</script>
	</body>
</html>
