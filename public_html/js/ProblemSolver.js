ProblemSolver =
(
	function (userId)
	{
		this.userId = userId;
		
		this.start = function ()
		{
			$('#problemSolverContainer').slideUp (320,
				function ()
				{
					$(this).html ('<div class="panel"><img src="/img/spinner.gif" alt="" /> Zoeken naar veelvoorkomende problemen...</div>').slideDown (240);
					
					$.get
					(
						'/problem-solver/scan?userId=' + userId,
						function (data)
						{
							alert (data);
							$('#problemSolverContainer').html ('Gevonden problemen: ');
							
							for (var i = 0; i < data.length; i++)
							{
								$('#problemSolverContainer').append (data[i].message + '<br />');
							}
						}
					)
				}
			);
		}
	}
)