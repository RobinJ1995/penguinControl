@extends ('layout.master')

@section ('pageTitle')
Serverinformatie &bull; Staff
@endsection

@section ('content')
<div class="phpinfo">
	{{ $info }}
</div>
@endsection