<div data-alert class="alert-box {{ $alert->type }}">
	{!! $alert->message !!}
	@if ($alert->close)
		<a href="#" class="close">&times;</a>
	@endif
</div>