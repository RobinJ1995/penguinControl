$(document).ready
(
	function ()
	{
		var editor = ace.edit ('editor');
		var textarea = $('textarea[name="content"]').hide ();
		
		editor.setTheme ('ace/theme/chrome');
		editor.getSession ().setTabSize (8);
		editor.getSession ().setUseWrapMode (false);
		editor.getSession ().setMode ('ace/mode/html');
		
		editor.getSession ().setValue (textarea.val ());
		editor.getSession ().on ('change',
			function ()
			{
				textarea.val (editor.getSession ().getValue ());
			}
		);
	}
);