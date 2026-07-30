$(document).ready
	(
		function()
		{
			$('#content').on('click', '.remove',
				function(e)
				{
					if (! confirm ('Are you sure you want to remove the selected item?'))
						e.preventDefault();
				}
			);
		}
	);