@extends ('layout.master')

@section ('pageTitle')
Logs &bull; Staff
@endsection

@section ('content')
<h2>{{ $log->message }}</h2>

@include ('staff.system.log.part.showData')
@endsection