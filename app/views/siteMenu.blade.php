<div class="contain-to-grid sticky">
	<nav id="siteMenu" class="top-bar" data-topbar data-options="sticky_on: large">
		<section class="top-bar-section">
			<ul>
				@foreach ($siteMenu as $item)
				<li>
					<a data-id="{{ $item->id }}" data-name="{{ $item->name }}" href="{{ action ('PageController@show', array ($item->name)) }}">{{ $item->title }}</a>
				</li>
				<li class="divider hide-for-small"></li>
				@endforeach
			</ul>
		</section>
	</nav>
</div>