ProblemSolver =
(
	function (userId)
	{
		this.userId = userId;
		
		this.start = function ()
		{
			var self = this;
			
			$('#problemSolverContainer').slideUp (320,
				function ()
				{
					$(this).html ('<div class="panel"><img src="/img/spinner.gif" alt="" /> Zoeken naar veelvoorkomende problemen...</div>').slideDown (240);
					
					$.get
					(
						'/problem-solver/schedule?userId=' + userId,
						function (data)
						{
							var taskId = data.taskId;
							
							self.checkSystemTask (taskId);
							
						}
					)
				}
			);
		}
		
		this.checkSystemTask = function (taskId)
		{
			var self = this;
			
			$.get
			(
				'/problem-solver/result?taskId=' + taskId,
				function (data)
				{
					if (data.lastRun > 0)
					{
						self.showResults (data);
					}
					else
					{
						setTimeout
						(
							function ()
							{
								self.checkSystemTask (taskId);
							},
							5000
						);
					}
				}
			);
		}
		
		this.showResults = function (data)
		{
			var table;
							
			if (data.length === 0)
			{
				table = 'Geen problemen gevonden';
			}
			else
			{
				table = 'Gevonden problemen:\n\
					<table>\n\
						<thead>\n\
							<tr>\n\
								<th>Probleem</th>\n\
								<th>Onderdeel</th>\n\
								<th>Opgelost?</th>\n\
							</tr>\n\
						</thead>\n\
						<tbody>\n';

				for (var i = 0; i < data.length; i++)
					table += '<tr>\n\
						<td>' + data[i].message + '</td>\n\
						<td>' + data[i].object + '</td>\n\
						<td>' + (data[i].fix == void 0 ? 'Niet opgelost' : data[i].fix) + '</td>\n\
						</tr>\n';
			}

			$('#problemSolverContainer').html (table);
		}
	}
)