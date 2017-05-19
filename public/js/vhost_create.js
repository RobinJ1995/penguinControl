$(document).ready
(
	() => {
		$('input[name="servername"]').on ('input',
			() => {
				let docroot = $('input[name="docroot"]').val ();
				
				if (docroot == '' || docroot.endsWith ('/public/'))
				{
					let servername = $('input[name="servername"]').val ();
					
					if (servername)
					{
						let name = servername.split ('.')[servername.startsWith ('www.') ? 1 : 0];
						
						$('input[name="docroot"]').val (name + '/public/');
					}
				}
			}
		);
	}
);
