@section ('content')
@foreach ($serviceStatuses as $serviceStatus)
<p>{{ var_dump ($serviceStatus) }}</p>
@endforeach
@endsection