$(document).ready
(
	function ()
	{
		$('#veld_rnummer').on ('input',
			function ()
			{
				$('#veld_email').val ($(this).val () + '@student.thomasmore.be');
			}
		);
	}
);