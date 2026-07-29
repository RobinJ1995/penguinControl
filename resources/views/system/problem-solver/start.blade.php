@extends ('layout.master')

@section ('pageTitle')
Problem solver
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
<p>The problem solver can try to fix common problems for you automatically. Click the button below to start it.</p>
<div id="problemSolverContainer">
	<p class="button" id="problemSolverStart">Start</p>
</div>
@endsection
