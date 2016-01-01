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
					)
				}
			);
		}
	}
)