$(document).ready
(
	function ()
	{
		$('#veld_rnummer').on ('input',
			function ()
			{
				var nummer = $(this).val ();
				
				if (nummer.startsWith ('c10'))
					$('#veld_email').val (nummer + '@hik.be');
				else
					$('#veld_email').val (nummer + '@student.thomasmore.be');
			}
		);
	}
);
