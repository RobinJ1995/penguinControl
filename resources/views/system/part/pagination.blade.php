<div class="pagination-centered">
	<ul class="pagination">
		{{ with (new PaginationPresenter ($paginator))->render (); }}
	</ul>
</div>