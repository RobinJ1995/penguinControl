<div id="modalSearch" class="reveal-modal" data-reveal>
	<h2>Search</h2>
	
	<form action="{{ $searchUrl }}" method="GET">
		<label>Search term:
			<input type="text" name="term" />
		</label>
		<label>User:
			<input type="text" name="username" />
		</label>
		
		<button>Search</button>
	</form>
	
	<a class="close-reveal-modal">&#215;</a>
</div>