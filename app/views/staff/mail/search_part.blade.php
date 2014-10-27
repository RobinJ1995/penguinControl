<div id="modalSearch" class="reveal-modal" data-reveal>
	<h2>Zoeken</h2>
	
	<form action="{{ $searchUrl }}" method="GET">
		<label>Zoekterm:
			<input type="text" name="term" />
		</label>
		<label>Gebruiker:
			<input type="text" name="username" />
		</label>
		
		<button>Zoeken</button>
	</form>
	
	<a class="close-reveal-modal">&#215;</a>
</div>