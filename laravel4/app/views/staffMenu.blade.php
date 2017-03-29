@if (! empty (Auth::user ()) && (Auth::user ()->getLowestGid () <= Group::where ('name', 'staff')->firstOrFail ()->gid))
<div class="contain-to-grid">
	<nav id="staffMenu" class="top-bar" data-topbar>
		<ul class="title-area">
			<li class="name"></li>
			<li class="toggle-topbar menu-icon">
				<a href="#">
					<span>Staff</span>
				</a>
			</li>
		</ul>
		
		<section class="top-bar-section">
			<ul class="right show-for-large-up">
				<li class="staffMenu">
					<a href="#"><strong>Staff</strong></a>
				</li>
			</ul>
			<ul>
				@foreach ($staffMenu as $item)
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
@endif