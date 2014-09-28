$(document).ready
	(
		function()
		{
			$('#content').on('click', '.remove',
				function(e)
				{
					if (! confirm ('Weet u zeker dat u het gekozen item wil verwijderen?'))
						e.preventDefault();
				}
			);
		}
	);