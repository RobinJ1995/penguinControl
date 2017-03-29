@foreach ($data as $key => $data)
<div class="panel">
	@if (! is_int ($key))
	<h3>{{ ucfirst ($key) }}</h3>
	@endif
	@if (is_array ($data))
	@include ('staff.system.log.part.showData')
	@else
	<kbd>{{ $data }}</kbd>
	@endif
</div>
@endforeach