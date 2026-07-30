@extends ('layout.master')

@section ('pageTitle')
Server information &bull; Staff
@endsection

@section ('content')
<div class="phpinfo">
	{{ $info }}
</div>
@endsection