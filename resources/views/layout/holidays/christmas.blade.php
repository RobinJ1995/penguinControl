<style type="text/css">
	body
	{
		background: url('/img/christmas-bg-tile.gif');
	}
	body > div.row, body > footer.row
	{
		background: white;
	}
</style>
<script src="/js/snowfall.jquery.js"></script>
<script type="text/javascript">
	$(document).ready
	(
		function ()
		{
			$(document).snowfall
			(
				{
					round: true,
					minSize: 2,
					maxSize: 8,
					flakeCount: 128
				}
			);
		}		
	);
</script>