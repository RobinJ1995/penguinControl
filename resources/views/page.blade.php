@extends ('layout.master')

@section ('pageTitle')
{{ $page->title }}
@endsection

@section ('controlMenu')
@endsection

@section ('siteMenu')
@include ('siteMenu')
@endsection

@section ('content')
{!! $page->content !!}
@endsection