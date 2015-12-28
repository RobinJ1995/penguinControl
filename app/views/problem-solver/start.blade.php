@extends ('layout.master')

@section ('pageTitle')
Probleemoplosser
@endsection

@section ('js')
@parent
<script src="/js/ProblemSolver.js"></script>
<script type="text/javascript">
	$(document).ready
	(
		function ()
		{
			var problemSolver = new ProblemSolver ({{ $userId }});
			
			$('#problemSolverStart').click
			(
				function ()
				{
					problemSolver.start ();
				}
			);
		}
	);
</script>
@endsection

@section ('content')
<p>De probleemoplosser kan automatisch veelvoorkomende problemen voor u proberen op te lossen. Klik op de knop hieronder om de probleemoplosser te starten.</p>
<div id="problemSolverContainer">
	<p class="button" id="problemSolverStart">Start</p>
</div>
@endsection
