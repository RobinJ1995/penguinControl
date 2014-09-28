<div class="contain-to-grid sticky">
	<nav id="controlMenu" class="top-bar" data-topbar data-options="sticky_on: large">
		<section class="top-bar-section">
			<ul>
				@foreach ($controlMenu as $item)
				<li class="{{ $item->hasChildren ? 'has-dropdown' : '' }}">
					<a data-id="{{ $item->id }}" href="{{ empty ($item->url) ? '#' : $item->url }}">{{ $item->name }}</a>
					@if ($item->hasChildren)
					<ul class="dropdown">
						@foreach ($item->children as $child)
						<li>
							<a data-id="{{ $child->id }}" data-parent="{{ $child->parent }}" href="{{ empty ($child->url) ? '#' : $child->url }}">{{ $child->name }}</a>
						</li>
						@endforeach
					</ul>
					@endif
				</li>
				<li class="divider hide-for-small"></li>
				@endforeach
			</ul>
		</section>
	</nav>
</div>